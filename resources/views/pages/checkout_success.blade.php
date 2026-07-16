<x-app-layout>
  <div class="container mx-auto p-6 md:p-12 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white border rounded-box shadow-lg p-8 text-center space-y-6 animate-fade-in">
      
      <!-- Big animated Checkmark -->
      <div class="flex justify-center">
        <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center border-2 border-green-500 shadow-sm animate-bounce">
          <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
      </div>

      <!-- Headers -->
      <div class="space-y-2">
        <h1 class="text-3xl font-extrabold text-gray-800">Pembayaran Sukses!</h1>
        <p class="text-gray-500 text-sm leading-relaxed px-2">
          Terima kasih atas pembelian Anda. Pemesanan tiket Anda telah berhasil diproses secara transaksional di database.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col gap-3 pt-4 border-t">
        <a href="{{ route('transactions.index') }}" class="btn btn-primary w-full text-white font-bold">
          Lihat Riwayat Transaksi
        </a>
        <a href="{{ route('home') }}" class="btn btn-outline border-gray-300 w-full text-gray-600 hover:bg-gray-50">
          Kembali ke Beranda
        </a>
      </div>
    </div>
  </div>
</x-app-layout>
