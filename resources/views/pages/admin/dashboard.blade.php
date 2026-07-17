@extends('layouts.admin_layouts')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto p-6 md:p-10 space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">Ringkasan Analitik</h1>
        <div class="text-sm text-gray-500">Data terupdate pada {{ now()->format('d M Y, H:i') }}</div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Card 1: Total Revenue -->
        <div class="card bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md p-6 rounded-box transition-all hover:scale-[1.02]">
            <div class="flex justify-between items-start mb-2">
                <span class="text-emerald-100 font-medium text-sm">Pendapatan Total</span>
                <span class="p-1.5 bg-emerald-400/30 rounded-md">💰</span>
            </div>
            <div class="text-2xl font-bold">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
            <div class="text-xs text-emerald-100 mt-2">Dari seluruh pesanan tiket</div>
        </div>

        <!-- Card 2: Tickets Sold -->
        <div class="card bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md p-6 rounded-box transition-all hover:scale-[1.02]">
            <div class="flex justify-between items-start mb-2">
                <span class="text-blue-100 font-medium text-sm">Tiket Terjual</span>
                <span class="p-1.5 bg-blue-400/30 rounded-md">🎟️</span>
            </div>
            <div class="text-3xl font-bold">{{ number_format($stats['total_tickets_sold'], 0, ',', '.') }}</div>
            <div class="text-xs text-blue-100 mt-2">Tiket terdistribusi</div>
        </div>

        <!-- Card 3: Total Events -->
        <div class="card bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-md p-6 rounded-box transition-all hover:scale-[1.02]">
            <div class="flex justify-between items-start mb-2">
                <span class="text-purple-100 font-medium text-sm">Total Event</span>
                <span class="p-1.5 bg-purple-400/30 rounded-md">🎪</span>
            </div>
            <div class="text-3xl font-bold">{{ $stats['total_events'] }}</div>
            <div class="text-xs text-purple-100 mt-2">{{ $stats['upcoming_events'] }} Event akan datang</div>
        </div>

        <!-- Card 4: Total Orders -->
        <div class="card bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md p-6 rounded-box transition-all hover:scale-[1.02]">
            <div class="flex justify-between items-start mb-2">
                <span class="text-amber-100 font-medium text-sm">Total Transaksi</span>
                <span class="p-1.5 bg-amber-400/30 rounded-md">🛍️</span>
            </div>
            <div class="text-3xl font-bold">{{ $stats['total_orders'] }}</div>
            <div class="text-xs text-amber-100 mt-2">Pesanan sukses diproses</div>
        </div>

        <!-- Card 5: Total Categories -->
        <div class="card bg-gradient-to-br from-gray-700 to-slate-800 text-white shadow-md p-6 rounded-box transition-all hover:scale-[1.02]">
            <div class="flex justify-between items-start mb-2">
                <span class="text-gray-200 font-medium text-sm">Total Kategori</span>
                <span class="p-1.5 bg-slate-600/30 rounded-md">🏷️</span>
            </div>
            <div class="text-3xl font-bold">{{ $stats['total_categories'] }}</div>
            <div class="text-xs text-gray-200 mt-2">Klasifikasi jenis event</div>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-blue-50 border border-blue-100 p-5 rounded-box flex items-center justify-between shadow-xs">
            <div>
                <div class="text-sm font-semibold text-blue-800">Upcoming Events</div>
                <div class="text-xs text-blue-600 mt-1">Akan diselenggarakan</div>
            </div>
            <span class="badge badge-info text-white text-lg font-bold p-3">{{ $stats['upcoming_events'] }}</span>
        </div>
        <div class="bg-green-50 border border-green-100 p-5 rounded-box flex items-center justify-between shadow-xs">
            <div>
                <div class="text-sm font-semibold text-green-800">Ongoing Events</div>
                <div class="text-xs text-green-600 mt-1">Sedang berlangsung</div>
            </div>
            <span class="badge badge-success text-white text-lg font-bold p-3">{{ $stats['ongoing_events'] }}</span>
        </div>
        <div class="bg-gray-100 border border-gray-200 p-5 rounded-box flex items-center justify-between shadow-xs">
            <div>
                <div class="text-sm font-semibold text-gray-800">Completed Events</div>
                <div class="text-xs text-gray-600 mt-1">Telah selesai dilaksanakan</div>
            </div>
            <span class="badge badge-neutral text-lg font-bold p-3">{{ $stats['completed_events'] }}</span>
        </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Events -->
        <div class="card bg-white shadow-xs p-6 rounded-box border">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Event Terbaru</h3>
                <a href="{{ route('admin.events.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Judul Event</th>
                            <th>Kategori</th>
                            <th>Tanggal & Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_events as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="font-medium text-gray-800 text-sm">{{ $event->judul }}</td>
                            <td>
                                <span class="badge badge-ghost badge-sm">{{ $event->kategori->nama ?? 'N/A' }}</span>
                            </td>
                            <td class="text-xs">
                                {{ $event->tanggal_waktu ? $event->tanggal_waktu->format('d M Y, H:i') : 'N/A' }}
                            </td>
                            <td>
                                @if($event->status === 'Upcoming')
                                    <span class="badge badge-info text-white badge-xs">{{ $event->status }}</span>
                                @elseif($event->status === 'Ongoing')
                                    <span class="badge badge-success text-white badge-xs">{{ $event->status }}</span>
                                @else
                                    <span class="badge badge-neutral badge-xs">{{ $event->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 text-sm py-4">Belum ada event tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card bg-white shadow-xs p-6 rounded-box border">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Transaksi Terbaru</h3>
                <span class="text-xs text-gray-400">5 Pesanan Terakhir</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Pembeli</th>
                            <th>Event</th>
                            <th>Tanggal Order</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent_orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td>
                                <div class="text-sm font-semibold text-gray-800">{{ $order->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-400">{{ $order->user->email ?? 'N/A' }}</div>
                            </td>
                            <td class="text-sm text-gray-700 max-w-[180px]">
                                <div class="font-semibold text-gray-800 truncate">{{ $order->events->judul ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-400 flex items-center gap-1 mt-0.5 truncate">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $order->events->lokasi ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="text-xs text-gray-500">
                                {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d M Y, H:i') : 'N/A' }}
                            </td>
                            <td class="font-bold text-gray-800 text-right text-sm">
                                Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 text-sm py-4">Belum ada transaksi diproses.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection