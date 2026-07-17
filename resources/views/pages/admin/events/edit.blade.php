@extends('layouts.admin_layouts')

@section('title', 'Edit Event')

@section('content')
<!-- Cropper.js CDNs -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<div class="container mx-auto p-10 max-w-4xl">
    <!-- Back Button & Title -->
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline btn-sm">
            ← Kembali
        </a>
        <h1 class="text-3xl font-semibold text-gray-800">Edit Event</h1>
    </div>

    <!-- Warning Alert if Event Has Sales -->
    @if ($hasSales)
        <div class="alert alert-warning shadow-xs mb-6 bg-yellow-100 border-yellow-300 text-yellow-800 p-4 rounded-box flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <span class="font-bold">Peringatan:</span> Event ini sudah memiliki penjualan tiket. Beberapa field mungkin tidak dapat diubah (seperti Tanggal & Waktu, serta penghapusan tiket yang sudah ada).
            </div>
        </div>
    @endif

    <!-- Error Alert -->
    @if ($errors->any())
        <div class="alert alert-error shadow-xs mb-6 bg-red-500 text-white p-4 rounded-box">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="card bg-white shadow-xs p-8 rounded-box">
        <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <h2 class="text-xl font-bold text-gray-700 border-b pb-2 mb-4">Informasi Event</h2>

            <!-- Grid 2 Columns for Event Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Judul Event -->
                <div class="space-y-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Judul Event</span>
                        <span class="text-error">*</span>
                    </label>
                    <input type="text" name="judul" value="{{ old('judul', $event->judul) }}" placeholder="Masukkan judul event" class="input input-bordered w-full" required>
                </div>

                <!-- Kategori Dropdown -->
                <div class="space-y-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Kategori</span>
                        <span class="text-error">*</span>
                    </label>
                    <select name="kategori_id" class="select select-bordered w-full" required>
                        <option value="" disabled>Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('kategori_id', $event->kategori_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Lokasi -->
                <div class="space-y-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Lokasi</span>
                        <span class="text-error">*</span>
                    </label>
                    <select name="lokasi_id" class="select select-bordered w-full" required>
                        <option value="" disabled>Pilih Lokasi</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('lokasi_id', $event->lokasi_id) == $location->id ? 'selected' : '' }}>
                                {{ $location->nama_lokasi }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal & Waktu (Readonly if hasSales) -->
                <div class="space-y-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Tanggal & Waktu</span>
                        @if ($hasSales)
                            <span class="text-xs text-error font-semibold block mt-0.5">(Terkunci karena tiket sudah terjual)</span>
                        @else
                            <span class="text-error">*</span>
                        @endif
                    </label>
                    <input type="datetime-local" name="tanggal_waktu" value="{{ old('tanggal_waktu', $event->tanggal_waktu ? $event->tanggal_waktu->format('Y-m-d\TH:i') : '') }}" class="input input-bordered w-full" {{ $hasSales ? 'readonly' : '' }} required>
                </div>

                <!-- Current Image Section -->
                <div class="space-y-2 md:col-span-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Gambar Event saat Ini</span>
                    </label>
                    <div class="border rounded-box p-2 w-full max-w-xs bg-gray-50">
                        <img src="{{ $event->image_url }}" alt="Current Image" class="w-full h-auto rounded-box object-cover">
                    </div>
                </div>

                <!-- Gambar File Input -->
                <div class="space-y-2 md:col-span-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Ubah Gambar Event</span>
                    </label>
                    <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/jpg,image/png" class="file-input file-input-bordered w-full">
                    <span class="text-xs text-gray-400 block mt-1">Kosongkan jika tidak ingin mengubah gambar. Format: JPG, JPEG, PNG. Ukuran Maksimal: 2MB.</span>
                </div>

                <!-- Image Preview Container -->
                <div id="image_preview_container" class="hidden md:col-span-2">
                    <label class="block mb-2">
                        <span class="text-sm font-medium text-gray-500">Pratinjau Gambar Baru</span>
                    </label>
                    <div class="border rounded-box p-2 w-full max-w-xs bg-gray-50">
                        <img id="image_preview" src="" alt="Pratinjau Gambar" class="w-full h-auto rounded-box object-cover">
                    </div>
                </div>

                <!-- Deskripsi (Span 2 Columns) -->
                <div class="space-y-2 md:col-span-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Deskripsi Event</span>
                        <span class="text-error">*</span>
                    </label>
                    <textarea name="deskripsi" rows="4" placeholder="Tuliskan deskripsi lengkap event di sini..." class="textarea textarea-bordered w-full" required>{{ old('deskripsi', $event->deskripsi) }}</textarea>
                </div>
            </div>

            <!-- Ticket Section -->
            <div class="border-t pt-6 mt-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-700">Informasi Tiket</h2>
                    <button type="button" id="btn_add_ticket" class="btn btn-outline btn-sm">
                        + Tambah Tiket
                    </button>
                </div>

                <!-- Ticket Cards Container -->
                <div id="ticket_container" class="space-y-4">
                    <!-- Dynamic ticket cards will be inserted here -->
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-3 pt-6 border-t mt-8">
                <a href="{{ route('admin.events.index') }}" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>

        </form>
    </div>

    <!-- Status History Card -->
    <div class="card bg-white shadow-xs p-8 rounded-box mt-8">
        <h2 class="text-xl font-bold text-gray-700 border-b pb-2 mb-4">Riwayat Status Event</h2>
        @if ($event->statusHistories && $event->statusHistories->count() > 0)
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Waktu Perubahan</th>
                            <th>Status Lama</th>
                            <th>Status Baru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($event->statusHistories as $history)
                        <tr>
                            <td>{{ $history->created_at ? $history->created_at->format('d M Y, H:i:s') : 'N/A' }}</td>
                            <td>
                                @if ($history->old_status)
                                    <span class="badge badge-neutral font-medium">{{ $history->old_status }}</span>
                                @else
                                    <span class="text-gray-400 font-medium">Baru Dibuat</span>
                                @endif
                            </td>
                            <td>
                                @if ($history->new_status === 'Upcoming')
                                    <span class="badge badge-info text-white font-medium">{{ $history->new_status }}</span>
                                @elseif ($history->new_status === 'Ongoing')
                                    <span class="badge badge-success text-white font-medium">{{ $history->new_status }}</span>
                                @else
                                    <span class="badge badge-neutral font-medium">{{ $history->new_status }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">Belum ada riwayat perubahan status.</p>
        @endif
    </div>
</div>

<!-- Cropper Modal -->
<dialog id="cropper_modal" class="modal">
    <div class="modal-box max-w-2xl">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Potong Gambar Event</h3>
        <div class="max-w-full max-h-[400px] overflow-hidden bg-gray-100 flex items-center justify-center rounded-box border">
            <img id="cropper_image" src="" alt="Potong Gambar" class="max-w-full max-h-full">
        </div>
        <div class="modal-action">
            <button type="button" class="btn btn-primary" id="btn_crop_save">Potong & Simpan</button>
            <button type="button" class="btn" id="btn_crop_cancel">Batal</button>
        </div>
    </div>
</dialog>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Image Cropping & Preview Logic
        const gambarInput = document.getElementById('gambar');
        const previewContainer = document.getElementById('image_preview_container');
        const previewImg = document.getElementById('image_preview');

        const cropperModal = document.getElementById('cropper_modal');
        const cropperImage = document.getElementById('cropper_image');
        const btnCropSave = document.getElementById('btn_crop_save');
        const btnCropCancel = document.getElementById('btn_crop_cancel');
        let cropper = null;
        let currentFile = null;

        gambarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                currentFile = file;
                const reader = new FileReader();
                reader.onload = function (e) {
                    cropperImage.src = e.target.result;
                    cropperModal.showModal();

                    if (cropper) {
                        cropper.destroy();
                    }

                    cropper = new Cropper(cropperImage, {
                        aspectRatio: 16 / 9,
                        viewMode: 1,
                        background: false
                    });
                }
                reader.readAsDataURL(file);
            }
        });

        btnCropSave.addEventListener('click', function () {
            if (cropper) {
                cropper.getCroppedCanvas({
                    width: 800,
                    height: 450
                }).toBlob(function (blob) {
                    if (blob && currentFile) {
                        const croppedFile = new File([blob], currentFile.name, { type: currentFile.type });

                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(croppedFile);
                        gambarInput.files = dataTransfer.files;

                        const previewReader = new FileReader();
                        previewReader.onload = function (e) {
                            previewImg.src = e.target.result;
                            previewContainer.classList.remove('hidden');
                        }
                        previewReader.readAsDataURL(croppedFile);
                    }
                    
                    cropperModal.close();
                    cropper.destroy();
                    cropper = null;
                }, currentFile.type);
            }
        });

        btnCropCancel.addEventListener('click', function () {
            gambarInput.value = '';
            previewContainer.classList.add('hidden');
            cropperModal.close();
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });

        // Dynamic Ticket Form Logic
        const ticketContainer = document.getElementById('ticket_container');
        const btnAddTicket = document.getElementById('btn_add_ticket');
        
        // Pass existing tickets and sales status from Blade to Javascript
        const existingTickets = @json($event->tikets);
        const hasSales = @json($hasSales);
        let ticketCount = 0;

        function addTicketCard(type = 'reguler', price = '', stock = '', id = null) {
            ticketCount++;
            const ticketIndex = ticketCount - 1;

            const card = document.createElement('div');
            card.className = 'card bg-gray-50 border p-5 rounded-box relative ticket-card';
            card.id = `ticket_card_${ticketIndex}`;
            
            // If the ticket has sales, disable deletion by hiding the delete button and displaying a badge
            const isSold = hasSales && id !== null;
            const deleteButtonHtml = isSold 
                ? `<span class="badge badge-success text-white font-medium">Sudah Terjual</span>` 
                : `<button type="button" class="btn btn-xs btn-ghost text-red-500 hover:bg-red-50 btn-remove-ticket" data-index="${ticketIndex}">Hapus</button>`;

            card.innerHTML = `
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h4 class="font-semibold text-gray-700 ticket-title">Tiket #${ticketCount}</h4>
                    ${deleteButtonHtml}
                </div>
                
                ${id !== null ? `<input type="hidden" name="tikets[${ticketIndex}][id]" value="${id}">` : ''}

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tipe Tiket -->
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Tipe Tiket</span>
                            <span class="text-error">*</span>
                        </label>
                        <select name="tikets[${ticketIndex}][tipe]" class="select select-bordered w-full" required>
                            <option value="reguler" ${type === 'reguler' ? 'selected' : ''}>Reguler</option>
                            <option value="premium" ${type === 'premium' ? 'selected' : ''}>Premium</option>
                        </select>
                    </div>
                    <!-- Harga -->
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Harga (Rp)</span>
                            <span class="text-error">*</span>
                        </label>
                        <input type="number" name="tikets[${ticketIndex}][harga]" value="${price}" min="0" placeholder="0" class="input input-bordered w-full" required>
                    </div>
                    <!-- Stok -->
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Stok</span>
                            <span class="text-error">*</span>
                        </label>
                        <input type="number" name="tikets[${ticketIndex}][stok]" value="${stock}" min="0" placeholder="0" class="input input-bordered w-full" required>
                    </div>
                </div>
            `;

            ticketContainer.appendChild(card);
            updateTicketTitles();
        }

        function updateTicketTitles() {
            const cards = ticketContainer.querySelectorAll('.ticket-card');
            cards.forEach((card, idx) => {
                card.querySelector('.ticket-title').textContent = `Tiket #${idx + 1}`;
                
                // Update inputs names indexes to make sure they are sequential
                const selectElement = card.querySelector('select[name^="tikets"]');
                if (selectElement) selectElement.name = `tikets[${idx}][tipe]`;
                
                const hargaElement = card.querySelector('input[name*="[harga]"]');
                if (hargaElement) hargaElement.name = `tikets[${idx}][harga]`;
                
                const stokElement = card.querySelector('input[name*="[stok]"]');
                if (stokElement) stokElement.name = `tikets[${idx}][stok]`;

                const idElement = card.querySelector('input[name*="[id]"]');
                if (idElement) idElement.name = `tikets[${idx}][id]`;
                
                // Update remove button index if it exists
                const removeBtn = card.querySelector('.btn-remove-ticket');
                if (removeBtn) {
                    removeBtn.dataset.index = idx;
                }
                card.id = `ticket_card_${idx}`;
            });
            // reset count index to reflect current total count
            ticketCount = cards.length;
        }

        // Load existing tickets or add 1 default if empty
        if (existingTickets && existingTickets.length > 0) {
            existingTickets.forEach(ticket => {
                addTicketCard(ticket.tipe, ticket.harga, ticket.stok, ticket.id);
            });
        } else {
            addTicketCard();
        }

        // Add ticket click event
        btnAddTicket.addEventListener('click', function () {
            addTicketCard();
        });

        // Remove ticket click event
        ticketContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('btn-remove-ticket')) {
                const index = e.target.dataset.index;
                const card = document.getElementById(`ticket_card_${index}`);
                
                // Allow removing only if there's more than 1 ticket card
                if (ticketContainer.querySelectorAll('.ticket-card').length > 1) {
                    card.remove();
                    updateTicketTitles();
                } else {
                    alert('Minimal harus memiliki satu tiket.');
                }
            }
        });
    });
</script>
@endsection
