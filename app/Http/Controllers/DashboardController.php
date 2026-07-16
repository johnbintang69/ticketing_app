<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\DetailOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'total_categories' => Kategori::count(),
            'total_events' => Event::count(),
            'total_orders' => Order::count(),
            'total_tickets_sold' => DetailOrder::sum('jumlah'),
            'total_revenue' => Order::sum('total_harga'),
            'upcoming_events' => Event::upcoming()->count(),
            'ongoing_events' => Event::ongoing()->count(),
            'completed_events' => Event::completed()->count(),
        ];

        $recent_events = Event::with('kategori')->latest()->take(5)->get();
        $recent_orders = Order::with(['user', 'events'])->latest()->take(5)->get();

        return view('pages.admin.dashboard', compact('stats', 'recent_events', 'recent_orders'));
    }
}