<?php

namespace App\Http\Controllers;

use App\Models\ManagementLokasi;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the locations.
     */
    public function index()
    {
        $locations = ManagementLokasi::paginate(10);

        return view('pages.admin.locations.index', compact('locations'));
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255|unique:management_lokasis,nama_lokasi',
            'is_active' => 'nullable|boolean',
        ]);

        ManagementLokasi::create([
            'nama_lokasi' => $request->nama_lokasi,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil ditambahkan!');
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255|unique:management_lokasis,nama_lokasi,' . $id,
            'is_active' => 'nullable|boolean',
        ]);

        $location = ManagementLokasi::findOrFail($id);
        $location->update([
            'nama_lokasi' => $request->nama_lokasi,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil diperbarui!');
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy($id)
    {
        $location = ManagementLokasi::findOrFail($id);
        
        // Prevent deletion if associated with events
        if ($location->events()->exists()) {
            return redirect()->route('locations.index')
                ->with('error', 'Gagal menghapus! Lokasi ini sedang digunakan oleh event.');
        }

        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil dihapus!');
    }
}
