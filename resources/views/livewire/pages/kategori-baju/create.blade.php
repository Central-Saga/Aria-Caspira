<?php

use Livewire\Volt\Component;


use App\Models\KategoriBaju;

new class extends Component {
    public $nama_kategori;

    public function rules()
    {
        return [
            'nama_kategori' => 'required|string|max:100',
        ];
    }

    public function save()
    {
        $this->validate();
        KategoriBaju::create([
            'nama_kategori' => $this->nama_kategori,
        ]);
        session()->flash('success', 'Kategori berhasil ditambahkan.');
        return redirect()->route('kategori-baju.index');
    }
}; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-100 via-pink-50 to-white py-10">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl p-8 border border-indigo-100">
        <h2 class="text-2xl font-bold text-indigo-700 mb-6 text-center drop-shadow">Tambah Kategori Baju</h2>
        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <label for="nama_kategori" class="block text-sm font-semibold text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" id="nama_kategori" wire:model.defer="nama_kategori" placeholder="Masukkan nama kategori..." class="w-full px-4 py-2 rounded-lg border border-indigo-200 focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition shadow-sm bg-indigo-50/50" />
                @error('nama_kategori')
                    <span class="text-red-600 text-xs">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('kategori-baju.index') }}" class="px-5 py-2 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white font-bold shadow hover:from-indigo-600 hover:to-pink-600 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>
