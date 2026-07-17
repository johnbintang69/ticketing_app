<?php

namespace App\Http\Controllers;

use App\Models\Tiket;
use App\Models\Order;
use App\Models\DetailOrder;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Show checkout form page.
     */
    public function show(Request $request, Tiket $tiket)
    {
        $tiket->load('event.lokasiModel');
        
        if ($tiket->event->lokasiModel && !$tiket->event->lokasiModel->is_active) {
            return redirect()->route('home')->with('error', 'Event ini tidak dapat dipesan karena lokasinya sedang tidak aktif.');
        }
        
        // Load all active payment methods
        $paymentMethods = MetodePembayaran::where('is_active', true)->get();

        return view('pages.checkout', compact('tiket', 'paymentMethods'));
    }

    /**
     * Store checkout transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tiket_id' => 'required|exists:tikets,id',
            'jumlah' => 'required|integer|min:1',
            'metode_pembayaran_id' => 'required|exists:metode_pembayarans,id',
        ]);

        $tiket = Tiket::findOrFail($request->tiket_id);
        $tiket->load('event.lokasiModel');

        if ($tiket->event->lokasiModel && !$tiket->event->lokasiModel->is_active) {
            return redirect()->route('home')->with('error', 'Event ini tidak dapat dipesan karena lokasinya sedang tidak aktif.');
        }

        if ($tiket->stok !== null && $tiket->stok < $request->jumlah) {
            return redirect()->back()
                ->with('error', 'Stok tiket tidak mencukupi!')
                ->withInput();
        }

        $totalPrice = $tiket->harga * $request->jumlah;

        DB::transaction(function () use ($request, $tiket, $totalPrice) {
            // Create Order
            $order = Order::create([
                'user_id' => auth()->id(),
                'event_id' => $tiket->event_id,
                'order_date' => now(),
                'total_harga' => $totalPrice,
                'metode_pembayaran_id' => $request->metode_pembayaran_id,
            ]);

            // Create Order Detail
            DetailOrder::create([
                'order_id' => $order->id,
                'tiket_id' => $tiket->id,
                'jumlah' => $request->jumlah,
                'subtotal_harga' => $totalPrice,
            ]);

            // Decrement Stock
            if ($tiket->stok !== null) {
                $tiket->decrement('stok', $request->jumlah);
            }
        });

        return redirect()->route('checkout.success');
    }

    /**
     * Show checkout success landing page.
     */
    public function success()
    {
        return view('pages.checkout_success');
    }
}
