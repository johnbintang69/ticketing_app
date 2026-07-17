<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Kategori;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage with events and categories.
     */
    public function index(Request $request)
    {
        // Get all categories for the filter pills
        $categories = Kategori::all();

        // Build event query
        $eventsQuery = Event::with(['kategori', 'tikets'])
            ->whereHas('lokasiModel', function ($q) {
                $q->where('is_active', true);
            });

        // Filter by category if specified
        if ($request->has('kategori') && $request->kategori) {
            $eventsQuery->where('kategori_id', $request->kategori);
        }

        // Get events with minimum ticket price
        $events = $eventsQuery->get()->map(function ($event) {
            // Add minimum ticket price to each event
            $event->tikets_min_harga = $event->tikets->min('harga') ?? 0;
            return $event;
        });

        return view('home', [
            'categories' => $categories,
            'events' => $events,
        ]);
    }

    /**
     * Display the logged-in user's transaction history.
     */
    public function transactions()
    {
        $orders = \App\Models\Order::where('user_id', auth()->id())
            ->with(['events', 'tikets'])
            ->latest()
            ->get();

        return view('pages.transactions', compact('orders'));
    }
}
