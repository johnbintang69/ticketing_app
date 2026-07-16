<?php

namespace App\Http\Controllers;

use App\Models\MetodePembayaran;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the payment methods.
     */
    public function index()
    {
        $methods = MetodePembayaran::paginate(10);

        return view('pages.admin.payment_methods.index', compact('methods'));
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:metode_pembayarans,nama',
            'tipe' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        MetodePembayaran::create([
            'nama' => $request->nama,
            'tipe' => $request->tipe,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil ditambahkan!');
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:metode_pembayarans,nama,' . $id,
            'tipe' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $method = MetodePembayaran::findOrFail($id);
        $method->update([
            'nama' => $request->nama,
            'tipe' => $request->tipe,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil diperbarui!');
    }

    /**
     * Remove the specified payment method from storage.
     */
    public function destroy($id)
    {
        $method = MetodePembayaran::findOrFail($id);
        $method->delete();

        return redirect()->route('payment-methods.index')
            ->with('success', 'Metode pembayaran berhasil dihapus!');
    }
}
