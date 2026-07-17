<x-app-layout>
  <div class="container mx-auto p-6 md:p-12 min-h-screen">
    <div class="max-w-4xl mx-auto space-y-8">
      <div class="flex justify-between items-center border-b pb-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Riwayat Transaksi</h1>
          <p class="text-sm text-gray-500 mt-1">Daftar pemesanan tiket event Anda</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline btn-sm">← Cari Event Lain</a>
      </div>

      @if($orders->count() > 0)
        <div class="space-y-6">
          @foreach($orders as $order)
            <div class="card bg-white shadow-md border hover:shadow-lg transition-shadow rounded-box overflow-hidden">
              <div class="p-6 md:p-8 flex flex-col md:flex-row gap-6">
                <!-- Event Image -->
                <div class="w-full md:w-48 h-32 rounded-box overflow-hidden bg-gray-100 flex-shrink-0">
                  <img 
                    src="{{ $order->events->image_url ?? asset('storage/konser.jpg') }}" 
                    alt="{{ $order->events->judul ?? 'Event' }}" 
                    class="w-full h-full object-cover"
                  />
                </div>

                <!-- Transaction Details -->
                <div class="flex-1 space-y-4">
                  <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-2">
                    <div>
                      <h2 class="text-xl font-bold text-gray-800">{{ $order->events->judul ?? 'Event' }}</h2>
                      <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-xs text-gray-400">
                        <span class="flex items-center gap-1 font-medium text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                          {{ $order->events->lokasi ?? 'N/A' }}
                        </span>
                        <span class="opacity-50">|</span>
                        <span>ID Transaksi: #{{ $order->id }}</span>
                        <span class="opacity-50">|</span>
                        <span>Tanggal Order: {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, H:i') }} WIB</span>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-xs text-gray-500 font-medium">Total Pembayaran</div>
                      <div class="text-lg font-extrabold text-blue-900">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</div>
                    </div>
                  </div>

                  <div class="border-t pt-4">
                    <div class="text-sm font-semibold text-gray-700 mb-2">Detail Tiket yang Dipesan:</div>
                    <ul class="space-y-2">
                      @foreach($order->tikets as $ticket)
                        <li class="flex justify-between items-center text-sm bg-gray-50 p-2.5 rounded-box border border-gray-100">
                          <div>
                            <span class="badge badge-primary badge-sm mr-2">{{ ucfirst($ticket->tipe) }}</span>
                            <span class="text-gray-700">{{ $ticket->pivot->jumlah }}x tiket</span>
                          </div>
                          <span class="font-bold text-gray-800">Rp {{ number_format($ticket->pivot->subtotal_harga, 0, ',', '.') }}</span>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="card bg-white p-12 text-center border shadow-xs rounded-box space-y-4">
          <div class="text-5xl">🎟️</div>
          <h2 class="text-xl font-bold text-gray-800">Belum Ada Transaksi</h2>
          <p class="text-gray-500 text-sm max-w-sm mx-auto">Anda belum pernah melakukan pemesanan tiket event. Yuk cari konser atau pameran impian Anda sekarang!</p>
          <a href="{{ route('home') }}" class="btn btn-primary">Telusuri Event</a>
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
