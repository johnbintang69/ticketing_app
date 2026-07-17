@extends('layouts.admin_layouts')

@section('title', 'Tambah Event')

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
        <h1 class="text-3xl font-semibold text-gray-800">Tambah Event</h1>
    </div>

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
        <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <h2 class="text-xl font-bold text-gray-700 border-b pb-2 mb-4">Informasi Event</h2>

            <!-- Grid 2 Columns for Event Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Judul Event -->
                <div class="space-y-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Judul Event</span>
                        <span class="text-error">*</span>
                    </label>
                    <input type="text" name="judul" value="{{ old('judul') }}" placeholder="Masukkan judul event" class="input input-bordered w-full" required>
                </div>

                <!-- Kategori Dropdown -->
                <div class="space-y-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Kategori</span>
                        <span class="text-error">*</span>
                    </label>
                    <select name="kategori_id" class="select select-bordered w-full" required>
                        <option value="" disabled selected>Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('kategori_id') == $category->id ? 'selected' : '' }}>
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
                        <option value="" disabled selected>Pilih Lokasi</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('lokasi_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->nama_lokasi }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal & Waktu -->
                <div class="space-y-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Tanggal & Waktu</span>
                        <span class="text-error">*</span>
                    </label>
                    <input type="datetime-local" name="tanggal_waktu" value="{{ old('tanggal_waktu') }}" class="input input-bordered w-full" required>
                </div>

                <!-- Gambar File Input -->
                <div class="space-y-2 md:col-span-2">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Gambar Event</span>
                    </label>
                    <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/jpg,image/png" class="file-input file-input-bordered w-full">
                    <span class="text-xs text-gray-400 block mt-1">Format: JPG, JPEG, PNG. Ukuran Maksimal: 2MB.</span>
                </div>

                <!-- Image Preview Container -->
                <div id="image_preview_container" class="hidden md:col-span-2">
                    <label class="block mb-2">
                        <span class="text-sm font-medium text-gray-500">Pratinjau Gambar</span>
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
                    <textarea name="deskripsi" rows="4" placeholder="Tuliskan deskripsi lengkap event di sini..." class="textarea textarea-bordered w-full" required>{{ old('deskripsi') }}</textarea>
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
                <button type="submit" class="btn btn-primary">Simpan Event</button>
            </div>

        </form>
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
        let ticketCount = 0;

        function addTicketCard(type = 'reguler', price = '', stock = '') {
            ticketCount++;
            const ticketIndex = ticketCount - 1;

            const card = document.createElement('div');
            card.className = 'card bg-gray-50 border p-5 rounded-box relative ticket-card';
            card.id = `ticket_card_${ticketIndex}`;
            card.innerHTML = `
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h4 class="font-semibold text-gray-700 ticket-title">Tiket #${ticketCount}</h4>
                    <button type="button" class="btn btn-xs btn-ghost text-red-500 hover:bg-red-50 btn-remove-ticket" data-index="${ticketIndex}">
                        Hapus
                    </button>
                </div>
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
                card.querySelector('select[name^="tikets"]').name = `tikets[${idx}][tipe]`;
                card.querySelector('input[name*="[harga]"]').name = `tikets[${idx}][harga]`;
                card.querySelector('input[name*="[stok]"]').name = `tikets[${idx}][stok]`;
                
                // Update button index
                card.querySelector('.btn-remove-ticket').dataset.index = idx;
                card.id = `ticket_card_${idx}`;
            });
            // reset count index to reflect current total count
            ticketCount = cards.length;
        }

        // Add 1 ticket by default
        addTicketCard();

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
