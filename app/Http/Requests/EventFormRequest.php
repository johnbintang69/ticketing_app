<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EventFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Event validation rules
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'tanggal_waktu' => 'required|date',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Tickets validation rules
            'tikets' => 'required|array|min:1',
            'tikets.*.tipe' => 'required|in:reguler,premium',
            'tikets.*.harga' => 'required|numeric|min:0',
            'tikets.*.stok' => 'required|integer|min:0',
            'tikets.*.id' => 'nullable|exists:tikets,id',
        ];
    }

    /**
     * Get the custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul event wajib diisi.',
            'judul.string' => 'Judul event harus berupa teks.',
            'judul.max' => 'Judul event maksimal 255 karakter.',
            
            'deskripsi.required' => 'Deskripsi event wajib diisi.',
            'deskripsi.string' => 'Deskripsi event harus berupa teks.',
            
            'lokasi.required' => 'Lokasi event wajib diisi.',
            'lokasi.string' => 'Lokasi event harus berupa teks.',
            'lokasi.max' => 'Lokasi event maksimal 255 karakter.',
            
            'kategori_id.required' => 'Kategori event wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            
            'tanggal_waktu.required' => 'Tanggal dan waktu event wajib diisi.',
            'tanggal_waktu.date' => 'Format tanggal dan waktu event tidak valid.',
            'tanggal_waktu.after' => 'Tanggal dan waktu event harus di masa depan.',
            
            'gambar.image' => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes' => 'Format gambar harus jpg, jpeg, atau png.',
            'gambar.max' => 'Ukuran gambar maksimal 2MB.',
            
            'tikets.required' => 'Minimal harus menambahkan satu tiket.',
            'tikets.array' => 'Data tiket harus berupa array.',
            'tikets.min' => 'Minimal harus menambahkan satu tiket.',
            
            'tikets.*.tipe.required' => 'Tipe tiket wajib diisi.',
            'tikets.*.tipe.in' => 'Tipe tiket harus berupa reguler atau premium.',
            
            'tikets.*.harga.required' => 'Harga tiket wajib diisi.',
            'tikets.*.harga.numeric' => 'Harga tiket harus berupa angka.',
            'tikets.*.harga.min' => 'Harga tiket tidak boleh negatif.',
            
            'tikets.*.stok.required' => 'Stok tiket wajib diisi.',
            'tikets.*.stok.integer' => 'Stok tiket harus berupa bilangan bulat.',
            'tikets.*.stok.min' => 'Stok tiket tidak boleh negatif.',
            
            'tikets.*.id.exists' => 'ID tiket yang dipilih tidak valid.',
        ];
    }
}
