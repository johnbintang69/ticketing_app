<x-app-layout>
  <div class="container mx-auto p-6 md:p-12 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <div class="border-b pb-4 mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Checkout Tiket</h1>
        <p class="text-sm text-gray-500 mt-1">Konfirmasi pesanan dan pilih metode pembayaran</p>
      </div>

      @if(session('error'))
        <div class="alert alert-error shadow-xs mb-6 bg-red-500 text-white p-4 rounded-box">
          <div><span>⚠️ {{ session('error') }}</span></div>
        </div>
      @endif

      <form method="POST" action="{{ route('checkout.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf
        <input type="hidden" name="tiket_id" value="{{ $tiket->id }}">

        <!-- Left Column: Details & Payment Methods -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Event Details Summary Card -->
          <div class="card bg-white p-6 rounded-box border shadow-xs">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Event</h2>
            <div class="flex gap-4">
              <div class="w-24 h-24 rounded-box overflow-hidden bg-gray-100 flex-shrink-0">
                <img 
                  src="{{ $tiket->event->image_url ?? asset('storage/konser.jpg') }}" 
                  alt="{{ $tiket->event->judul }}" 
                  class="w-full h-full object-cover"
                />
              </div>
              <div class="space-y-1">
                <h3 class="font-bold text-gray-800 text-lg">{{ $tiket->event->judul }}</h3>
                <p class="text-xs text-gray-500">📍 {{ $tiket->event->lokasi }}</p>
                <p class="text-xs text-gray-500">📅 {{ $tiket->event->tanggal_waktu ? $tiket->event->tanggal_waktu->format('d M Y, H:i') : 'N/A' }} WIB</p>
                <div class="badge badge-primary badge-sm mt-1">Tiket {{ ucfirst($tiket->tipe) }}</div>
              </div>
            </div>
          </div>

          <!-- Select Payment Method Card -->
          <div class="card bg-white p-6 rounded-box border shadow-xs">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Pilih Metode Pembayaran</h2>
            @if($paymentMethods->count() > 0)
              <div class="relative w-full">
                <!-- Hidden Input to store selection -->
                <input type="hidden" name="metode_pembayaran_id" id="selected_metode_id" required>

                <!-- Custom Dropdown using Details/Summary -->
                <details class="dropdown w-full" id="payment_dropdown">
                  <summary class="btn btn-outline border-gray-300 w-full flex justify-between items-center bg-white hover:bg-gray-50 text-gray-700 font-medium normal-case" id="dropdown_trigger">
                    <span id="selected_method_label">-- Pilih Metode Pembayaran --</span>
                    <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </summary>
                  <ul class="menu dropdown-content bg-base-100 rounded-box z-10 w-full p-2 shadow-lg border border-gray-100 mt-1 max-h-60 overflow-y-auto">
                    @foreach($paymentMethods as $method)
                      <li>
                        <button type="button" class="flex justify-between items-center py-3 px-4 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg text-left" onclick="selectPaymentMethod({{ $method->id }}, '{{ $method->nama }}', '{{ $method->tipe }}')">
                          <span class="font-bold">{{ $method->nama }}</span>
                          <span class="badge badge-neutral badge-sm">{{ $method->tipe }}</span>
                        </button>
                      </li>
                    @endforeach
                  </ul>
                </details>
              </div>
            @else
              <div class="text-center py-6 text-gray-500 bg-gray-50 border rounded-box">
                Belum ada metode pembayaran yang diaktifkan oleh admin.
              </div>
            @endif
          </div>
        </div>

        <!-- Right Column: Pricing & Quantity -->
        <div class="space-y-6">
          <div class="card bg-white p-6 rounded-box border shadow-md space-y-6 sticky top-4">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-2">Rincian Pembayaran</h2>
            
            <!-- Quantity Input -->
            <div class="form-control">
              <label class="label"><span class="label-text font-semibold">Jumlah Tiket</span></label>
              <div class="flex gap-2 items-center">
                <input 
                  type="number" 
                  name="jumlah" 
                  id="jumlah_tiket" 
                  value="1" 
                  min="1" 
                  max="{{ $tiket->stok ?? 99 }}" 
                  class="input input-bordered w-full"
                  required
                />
                @if($tiket->stok !== null)
                  <span class="text-xs text-gray-400 w-24">Stok: {{ $tiket->stok }}</span>
                @endif
              </div>
            </div>

            <!-- Price Breakdown -->
            <div class="space-y-3 pt-4 border-t text-sm">
              <div class="flex justify-between text-gray-600">
                <span>Harga Satuan</span>
                <span>Rp {{ number_format($tiket->harga, 0, ',', '.') }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Jumlah</span>
                <span id="label_jumlah">1x</span>
              </div>
              <div class="flex justify-between font-extrabold text-gray-800 text-base pt-3 border-t">
                <span>Total Bayar</span>
                <span class="text-blue-900" id="label_total">Rp {{ number_format($tiket->harga, 0, ',', '.') }}</span>
              </div>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit" 
              class="btn btn-primary w-full text-white mt-4"
              {{ $paymentMethods->count() === 0 ? 'disabled' : '' }}
            >
              Proses Pembelian
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    function selectPaymentMethod(id, nama, tipe) {
      document.getElementById('selected_metode_id').value = id;
      document.getElementById('selected_method_label').innerHTML = `
        <span class="flex items-center gap-2">
          <span class="font-bold text-gray-800">${nama}</span>
          <span class="badge badge-neutral badge-xs">${tipe}</span>
        </span>
      `;
      
      // Close the details dropdown
      const details = document.getElementById('payment_dropdown');
      if (details) {
        details.removeAttribute('open');
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      const inputJumlah = document.getElementById('jumlah_tiket');
      const labelJumlah = document.getElementById('label_jumlah');
      const labelTotal = document.getElementById('label_total');
      const hargaSatuan = {{ $tiket->harga }};

      function formatRupiah(num) {
        return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      }

      if (inputJumlah) {
        inputJumlah.addEventListener('input', function () {
          let qty = parseInt(this.value);
          if (isNaN(qty) || qty < 1) {
            qty = 1;
          }
          labelJumlah.textContent = qty + 'x';
          labelTotal.textContent = formatRupiah(hargaSatuan * qty);
        });
      }
    });
  </script>
</x-app-layout>
