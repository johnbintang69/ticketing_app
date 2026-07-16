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
                      <p class="text-xs text-gray-400 mt-1">ID Transaksi: #{{ $order->id }} | Tanggal Order: {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, H:i') }} WIB</p>
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
