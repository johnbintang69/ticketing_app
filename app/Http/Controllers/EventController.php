<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Kategori;
use App\Http\Requests\EventFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['kategori', 'tikets']);

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%')
                  ->orWhereHas('lokasiModel', function ($l) use ($search) {
                      $l->where('nama_lokasi', 'like', '%' . $search . '%');
                  });
            });
        }

        $sort = $request->get('sort', 'asc');
        $query->orderBy('tanggal_waktu', $sort);

        $events = $query->paginate(10);

        if ($request->wantsJson() || !$request->acceptsHtml()) {
            return response()->json($events);
        }

        $categories = Kategori::all();

        return view('pages.admin.events.index', compact('events', 'categories'));
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        // Load the event with its relationships
        $event->load(['kategori', 'tikets']);

        // Related events (kategori sama, tanggal > now, max 4 events)
        $relatedEvents = Event::with('tikets')->where('kategori_id', $event->kategori_id)
            ->where('id', '!=', $event->id)
            ->upcoming()
            ->take(4)
            ->get();

        return view('events.show', [
            'event' => $event,
            'relatedEvents' => $relatedEvents,
        ]);
    }

    public function create()
    {
        $categories = Kategori::all();
        $locations = \App\Models\ManagementLokasi::where('is_active', true)->get();
        return view('pages.admin.events.create', compact('categories', 'locations'));
    }

    public function store(EventFormRequest $request)
    {
        $validated = $request->validated();

        $imagePath = 'konser.jpg';
        if ($request->hasFile('gambar')) {
            $imagePath = $request->file('gambar')->store('events', 'public');
        }

        // Create the event
        $event = Event::create([
            'user_id' => auth()->id(),
            'kategori_id' => $validated['kategori_id'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'lokasi_id' => $validated['lokasi_id'],
            'gambar' => $imagePath,
            'tanggal_waktu' => $validated['tanggal_waktu'],
        ]);

        // Create the tickets
        foreach ($validated['tikets'] as $ticketData) {
            $event->tikets()->create([
                'tipe' => $ticketData['tipe'],
                'harga' => $ticketData['harga'],
                'stok' => $ticketData['stok'],
            ]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil ditambahkan!');
    }

    public function edit(Event $event)
    {
        $categories = Kategori::all();
        $locations = \App\Models\ManagementLokasi::where('is_active', true)->get();
        $event->load(['tikets', 'statusHistories' => function($q) {
            $q->latest();
        }]);
        $hasSales = $event->hasSales();

        return view('pages.admin.events.edit', compact('event', 'categories', 'locations', 'hasSales'));
    }

    public function update(EventFormRequest $request, Event $event)
    {
        $validated = $request->validated();

        // 2. Jika event sudah terjual (hasSales()): Tampilkan error jika tanggal_waktu berubah
        if ($event->hasSales()) {
            $oldTime = $event->tanggal_waktu->format('Y-m-d H:i:s');
            $newTime = date('Y-m-d H:i:s', strtotime($validated['tanggal_waktu']));
            if ($oldTime !== $newTime) {
                return redirect()->back()
                    ->withErrors(['tanggal_waktu' => 'Tanggal dan waktu event tidak dapat diubah karena tiket sudah ada yang terjual.'])
                    ->withInput();
            }
        }

        // 3. Handle image update (hapus old image jika ada)
        $imagePath = $event->gambar;
        if ($request->hasFile('gambar')) {
            // Delete old image if it's not the default image
            if ($event->gambar && $event->gambar !== 'konser.jpg') {
                Storage::disk('public')->delete($event->gambar);
            }
            $imagePath = $request->file('gambar')->store('events', 'public');
        }

        // 4. Update event data
        $event->update([
            'kategori_id' => $validated['kategori_id'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'lokasi_id' => $validated['lokasi_id'],
            'gambar' => $imagePath,
            'tanggal_waktu' => $validated['tanggal_waktu'],
        ]);

        // 5. Handle tickets:
        $sentTicketIds = collect($validated['tikets'])->pluck('id')->filter()->toArray();

        // Delete removed tickets (only if no sales)
        if (!$event->hasSales()) {
            $event->tikets()->whereNotIn('id', $sentTicketIds)->delete();
        }

        // Update or create tickets
        foreach ($validated['tikets'] as $ticketData) {
            if (!empty($ticketData['id'])) {
                $event->tikets()->where('id', $ticketData['id'])->update([
                    'tipe' => $ticketData['tipe'],
                    'harga' => $ticketData['harga'],
                    'stok' => $ticketData['stok'],
                ]);
            } else {
                $event->tikets()->create([
                    'tipe' => $ticketData['tipe'],
                    'harga' => $ticketData['harga'],
                    'stok' => $ticketData['stok'],
                ]);
            }
        }

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil diperbarui!');
    }

    public function destroy(Event $event)
    {
        if ($event->hasSales()) {
            return redirect()->route('admin.events.index')->with('error', 'Event tidak dapat dihapus karena tiket sudah ada yang terjual.');
        }

        // Hapus image dari storage jika bukan default
        if ($event->gambar && $event->gambar !== 'konser.jpg') {
            Storage::disk('public')->delete($event->gambar);
        }

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus!');
    }

    public function export()
    {
        $events = Event::with(['kategori', 'tikets'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="events_export_' . now()->format('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($events) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, [
                'ID',
                'Judul',
                'Kategori',
                'Tanggal & Waktu',
                'Lokasi',
                'Deskripsi',
                'Status',
                'Detail Tiket',
                'Total Tiket Terjual'
            ]);

            foreach ($events as $event) {
                // Compile ticket details
                $ticketDetails = $event->tikets->map(function($t) {
                    return ucfirst($t->tipe) . ': ' . $t->stok . ' @ Rp' . number_format($t->harga, 0, ',', '.');
                })->implode(' | ');

                // Count total tickets sold
                $totalSold = \App\Models\DetailOrder::whereIn('tiket_id', $event->tikets->pluck('id'))->sum('jumlah');

                fputcsv($file, [
                    $event->id,
                    $event->judul,
                    $event->kategori->nama ?? 'N/A',
                    $event->tanggal_waktu ? $event->tanggal_waktu->format('Y-m-d H:i') : 'N/A',
                    $event->lokasi,
                    $event->deskripsi,
                    $event->status,
                    $ticketDetails,
                    $totalSold
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:events,id',
        ]);

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($request->event_ids as $id) {
            $event = Event::find($id);
            if ($event) {
                if ($event->hasSales()) {
                    $skippedCount++;
                } else {
                    // Delete image from storage if not default
                    if ($event->gambar && $event->gambar !== 'konser.jpg') {
                        Storage::disk('public')->delete($event->gambar);
                    }
                    $event->delete();
                    $deletedCount++;
                }
            }
        }

        if ($skippedCount > 0) {
            return redirect()->route('admin.events.index')
                ->with('success', "$deletedCount event berhasil dihapus.")
                ->with('error', "$skippedCount event dilewati karena sudah memiliki penjualan.");
        }

        return redirect()->route('admin.events.index')
            ->with('success', "Semua ($deletedCount) event terpilih berhasil dihapus.");
    }

    public function clone(Event $event)
    {
        $clonedEvent = $event->replicate();
        $clonedEvent->judul = "[Clone] " . $event->judul;
        $clonedEvent->save();

        foreach ($event->tikets as $ticket) {
            $clonedTicket = $ticket->replicate();
            $clonedTicket->event_id = $clonedEvent->id;
            $clonedTicket->save();
        }

        return redirect()->route('admin.events.index')->with('success', "Event '{$event->judul}' berhasil digandakan!");
    }
}