@extends('layouts.admin_layouts')

@section('title', 'Manajemen Kategori')

@section('content')

    <div class="container mx-auto p-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-semibold text-gray-800">Manajemen Kategori</h1>
            <button class="btn btn-primary" onclick="add_modal.showModal()">Tambah Kategori</button>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-xs mb-6">
                <div>
                    <span>✅ {{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs">
            <table class="table w-full">
                <!-- head -->
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Nama Kategori</th>
                        <th class="w-48 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $index => $category)
                    <tr class="hover:bg-gray-50">
                        <th>{{ $categories->firstItem() + $index }}</th>
                        <td class="font-medium text-gray-800">{{ $category->nama }}</td>
                        <td class="text-right">
                            <button class="btn btn-xs btn-primary mr-2" onclick="openEditModal(this)" data-id="{{ $category->id }}" data-nama="{{ $category->nama }}">Edit</button>
                            <button class="btn btn-xs bg-red-500 text-white hover:bg-red-600" onclick="openDeleteModal(this)" data-id="{{ $category->id }}">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-6 text-gray-500">Tidak ada kategori tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            {{ $categories->links() }}
        </div>
    </div>

    <!-- Add Category Modal -->
    <dialog id="add_modal" class="modal">
        <form method="POST" action="{{ route('categories.store') }}" class="modal-box">
            @csrf
            <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Kategori</h3>
            <div class="form-control w-full mb-4">
                <label class="label mb-2">
                    <span class="label-text">Nama Kategori</span>
                </label>
                <input type="text" placeholder="Masukkan nama kategori" class="input input-bordered w-full" name="nama" required />
            </div>
            <div class="modal-action">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn" onclick="add_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <!-- Edit Category Modal -->
     <dialog id="edit_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('PUT')

            <input type="hidden" name="category_id" id="edit_category_id">

            <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Kategori</h3>
            <div class="form-control w-full mb-4">
                <label class="label mb-2">
                    <span class="label-text">Nama Kategori</span>
                </label>
                <input type="text" placeholder="Masukkan nama kategori" class="input input-bordered w-full" id="edit_category_name" name="nama" required />
            </div>
            <div class="modal-action">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn" onclick="edit_modal.close()" type="reset">Batal</button>
            </div>
        </form>
    </dialog>

    <!-- Delete Modal -->
    <dialog id="delete_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('DELETE')

            <input type="hidden" name="category_id" id="delete_category_id">

            <h3 class="text-lg font-bold text-gray-800 mb-4">Hapus Kategori</h3>
            <p class="text-gray-600">Apakah Anda yakin ingin menghapus kategori ini?</p>
            <div class="modal-action">
                <button class="btn bg-red-500 text-white hover:bg-red-600" type="submit">Hapus</button>
                <button class="btn" onclick="delete_modal.close()" type="button">Batal</button>
            </div>
        </form>
    </dialog>

    <script>
        function openEditModal(button) {
            const name = button.dataset.nama;
            const id = button.dataset.id;
            const form = document.querySelector('#edit_modal form');
            
            document.getElementById("edit_category_name").value = name;
            document.getElementById("edit_category_id").value = id;

            // Set action with ID
            form.action = `{{ url('/admin/categories') }}/${id}`

            edit_modal.showModal();
        }

        function openDeleteModal(button) {
            const id = button.dataset.id;
            const form = document.querySelector('#delete_modal form');
            document.getElementById("delete_category_id").value = id;

            // Set action with ID
            form.action = `{{ url('/admin/categories') }}/${id}`

            delete_modal.showModal();
        }
    </script>

@endsection
