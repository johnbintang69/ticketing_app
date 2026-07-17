@extends('layouts.admin_layouts')

@section('title', 'Manajemen Lokasi')

@section('content')

    <div class="container mx-auto p-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-semibold text-gray-800">Manajemen Lokasi</h1>
            <button class="btn btn-primary" onclick="add_modal.showModal()">Tambah Lokasi</button>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-xs mb-6">
                <div>
                    <span>✅ {{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error shadow-xs mb-6 bg-red-500 text-white p-4 rounded-box">
                <div>
                    <span>⚠️ {{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs">
            <table class="table w-full">
                <!-- head -->
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Nama Lokasi</th>
                        <th>Status</th>
                        <th class="w-48 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $index => $location)
                    <tr class="hover:bg-gray-50">
                        <th>{{ $locations->firstItem() + $index }}</th>
                        <td class="font-bold text-gray-800">{{ $location->nama_lokasi }}</td>
                        <td>
                            @if ($location->is_active)
                                <span class="badge badge-success text-white font-medium">Aktif</span>
                            @else
                                <span class="badge badge-neutral font-medium">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <button class="btn btn-xs btn-primary mr-2" 
                                    onclick="openEditModal(this)" 
                                    data-id="{{ $location->id }}" 
                                    data-nama="{{ $location->nama_lokasi }}"
                                    data-status="{{ $location->is_active ? 1 : 0 }}">Edit</button>
                            <button class="btn btn-xs bg-red-500 text-white hover:bg-red-600" 
                                    onclick="openDeleteModal(this)" 
                                    data-id="{{ $location->id }}">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-gray-500">Tidak ada lokasi tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            {{ $locations->links() }}
        </div>
    </div>

    <!-- Add Location Modal -->
    <dialog id="add_modal" class="modal">
        <form method="POST" action="{{ route('locations.store') }}" class="modal-box max-w-xl">
            @csrf
            <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Lokasi</h3>
            
            <div class="space-y-4">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-semibold">Nama Lokasi</span></label>
                    <input type="text" placeholder="Contoh: Stadion Utama, Galeri Seni, Grand Ballroom" class="input input-bordered w-full" name="nama_lokasi" required />
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" class="checkbox" name="is_active" value="1" checked />
                        <span class="label-text font-semibold">Aktifkan lokasi ini</span>
                    </label>
                </div>
            </div>

            <div class="modal-action">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn" onclick="add_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <!-- Edit Location Modal -->
     <dialog id="edit_modal" class="modal">
        <form method="POST" class="modal-box max-w-xl">
            @csrf
            @method('PUT')

            <input type="hidden" name="location_id" id="edit_location_id">

            <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Lokasi</h3>
            
            <div class="space-y-4">
                <div class="form-control w-full">
                    <label class="label"><span class="label-text font-semibold">Nama Lokasi</span></label>
                    <input type="text" id="edit_nama" class="input input-bordered w-full" name="nama_lokasi" required />
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" class="checkbox" id="edit_status" name="is_active" value="1" />
                        <span class="label-text font-semibold">Aktifkan lokasi ini</span>
                    </label>
                </div>
            </div>

            <div class="modal-action">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn" onclick="edit_modal.close()" type="button">Batal</button>
            </div>
        </form>
    </dialog>

    <!-- Delete Modal -->
    <dialog id="delete_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('DELETE')

            <input type="hidden" name="location_id" id="delete_location_id">

            <h3 class="text-lg font-bold text-gray-800 mb-4">Hapus Lokasi</h3>
            <p class="text-gray-600">Apakah Anda yakin ingin menghapus lokasi ini?</p>
            <div class="modal-action">
                <button class="btn bg-red-500 text-white hover:bg-red-600" type="submit">Hapus</button>
                <button class="btn" onclick="delete_modal.close()" type="button">Batal</button>
            </div>
        </form>
    </dialog>

    <script>
        function openEditModal(button) {
            const id = button.dataset.id;
            const nama = button.dataset.nama;
            const status = parseInt(button.dataset.status);

            const form = document.querySelector('#edit_modal form');
            
            document.getElementById("edit_location_id").value = id;
            document.getElementById("edit_nama").value = nama;
            document.getElementById("edit_status").checked = (status === 1);

            // Set action with ID
            form.action = `{{ url('/admin/locations') }}/${id}`

            edit_modal.showModal();
        }

        function openDeleteModal(button) {
            const id = button.dataset.id;
            const form = document.querySelector('#delete_modal form');
            document.getElementById("delete_location_id").value = id;

            // Set action with ID
            form.action = `{{ url('/admin/locations') }}/${id}`

            delete_modal.showModal();
        }
    </script>

@endsection
