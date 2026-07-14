@extends('layouts.admin_layouts')

@section('title', 'Manajemen Event')

@section('content')

    <div class="container mx-auto p-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-semibold text-gray-800">Manajemen Event</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.events.export') }}" class="btn btn-outline">Export Excel</a>
                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Tambah Event</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-xs mb-6">
                <div>
                    <span>✅ {{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error shadow-xs mb-6 bg-red-500 text-white">
                <div>
                    <span>⚠️ {{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Filter Form -->
        <div class="bg-white p-6 rounded-box shadow-xs mb-6">
            <form method="GET" action="{{ route('admin.events.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- Search Input -->
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">Cari Event</span>
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul or lokasi..." class="input input-bordered w-full" />
                </div>

                <!-- Kategori Dropdown -->
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">Kategori</span>
                    </label>
                    <select name="kategori_id" class="select select-bordered w-full">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('kategori_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Dropdown -->
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text">Urutan Tanggal</span>
                    </label>
                    <select name="sort" class="select select-bordered w-full">
                        <option value="asc" {{ request('sort', 'asc') == 'asc' ? 'selected' : '' }}>Terdekat (Ascending)</option>
                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Terjauh (Descending)</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary flex-1">Filter</button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>

        <!-- Bulk Action Form -->
        <form id="bulk_delete_form" method="POST" action="{{ route('admin.events.bulk-destroy') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus event terpilih?')">
            @csrf
            
            <!-- Bulk Action Bar -->
            <div id="bulk_action_bar" class="hidden mb-4 p-4 bg-blue-50 border border-blue-200 rounded-box flex justify-between items-center transition-all duration-300">
                <span class="text-sm font-medium text-blue-800" id="selected_count">0 event terpilih</span>
                <button type="submit" class="btn bg-red-500 text-white hover:bg-red-600 btn-sm">Hapus Terpilih</button>
            </div>

            <!-- Table Events -->
            <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs">
                <table class="table w-full">
                    <!-- head -->
                    <thead>
                        <tr>
                            <th class="w-12">
                                <input type="checkbox" id="select_all" class="checkbox">
                            </th>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal & Waktu</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                        <tr class="hover:bg-gray-50">
                            <td>
                                <input type="checkbox" name="event_ids[]" value="{{ $event->id }}" class="checkbox event-checkbox">
                            </td>
                            <td>
                                <div class="avatar">
                                    <div class="mask mask-squircle w-16 h-16">
                                        <img src="{{ $event->image_url }}" alt="{{ $event->judul }}" class="object-cover" />
                                    </div>
                                </div>
                            </td>
                            <td class="font-semibold text-gray-800">{{ $event->judul }}</td>
                            <td>
                                <span class="badge badge-ghost">{{ $event->kategori->nama ?? 'N/A' }}</span>
                            </td>
                            <td>
                                {{ $event->tanggal_waktu ? $event->tanggal_waktu->format('d M Y, H:i') : 'N/A' }}
                            </td>
                            <td>📍 {{ $event->lokasi }}</td>
                            <td>
                                @if ($event->status === 'Upcoming')
                                    <span class="badge badge-info text-white font-medium">{{ $event->status }}</span>
                                @elseif ($event->status === 'Ongoing')
                                    <span class="badge badge-success text-white font-medium">{{ $event->status }}</span>
                                @else
                                    <span class="badge badge-neutral font-medium">{{ $event->status }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('events.show', $event) }}" target="_blank" class="btn btn-xs btn-ghost text-blue-600">View</a>
                                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-xs btn-primary">Edit</a>
                                    <button type="button" class="btn btn-xs btn-outline" onclick="event.preventDefault(); document.getElementById('clone_form_{{ $event->id }}').submit();">Clone</button>
                                    <button type="button" class="btn btn-xs bg-red-500 text-white hover:bg-red-600" onclick="openDeleteModal(this)" data-id="{{ $event->id }}">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-6 text-gray-500">Tidak ada event yang ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Hidden Clone Forms Container -->
        @foreach ($events as $event)
            <form id="clone_form_{{ $event->id }}" method="POST" action="{{ route('admin.events.clone', $event) }}" class="hidden">
                @csrf
            </form>
        @endforeach

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            {{ $events->appends(request()->except('page'))->links() }}
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <dialog id="delete_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('DELETE')

            <h3 class="text-lg font-bold text-gray-800 mb-4">Hapus Event</h3>
            <p class="text-gray-600">Apakah Anda yakin ingin menghapus event ini? Semua tiket terkait juga akan dihapus.</p>
            <div class="modal-action">
                <button class="btn bg-red-500 text-white hover:bg-red-600" type="submit">Hapus</button>
                <button class="btn" type="button" onclick="delete_modal.close()">Batal</button>
            </div>
        </form>
    </dialog>

    <script>
        function openDeleteModal(button) {
            const id = button.dataset.id;
            const form = document.querySelector('#delete_modal form');
            
            // Set action url dynamically
            form.action = `{{ url('/admin/events') }}/${id}`;

            delete_modal.showModal();
        }

        // Bulk Delete Actions Script
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select_all');
            const checkboxes = document.querySelectorAll('.event-checkbox');
            const bulkActionBar = document.getElementById('bulk_action_bar');
            const selectedCountSpan = document.getElementById('selected_count');

            function toggleBulkActionBar() {
                const checkedCheckboxes = document.querySelectorAll('.event-checkbox:checked');
                const checkedCount = checkedCheckboxes.length;

                if (checkedCount > 0) {
                    selectedCountSpan.textContent = `${checkedCount} event terpilih`;
                    bulkActionBar.classList.remove('hidden');
                } else {
                    bulkActionBar.classList.add('hidden');
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(cb => {
                        cb.checked = selectAll.checked;
                    });
                    toggleBulkActionBar();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    // Update select_all state
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    const someChecked = Array.from(checkboxes).some(c => c.checked);
                    
                    selectAll.checked = allChecked;
                    selectAll.indeterminate = someChecked && !allChecked;

                    toggleBulkActionBar();
                });
            });
        });
    </script>

@endsection
