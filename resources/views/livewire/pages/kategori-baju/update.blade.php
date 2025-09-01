<?php

use Livewire\Volt\Component;


use App\Models\KategoriBaju;

new class extends Component {
    public $kategori;
    public $nama_kategori;

    public function mount($id)
    {
        $this->kategori = KategoriBaju::findOrFail($id);
        $this->nama_kategori = $this->kategori->nama_kategori;
    }

    public function rules()
    {
        return [
            'nama_kategori' => 'required|string|max:100',
        ];
    }

    public function update()
    {
        $this->validate();
        $this->kategori->update([
            'nama_kategori' => $this->nama_kategori,
        ]);
        session()->flash('success', 'Kategori berhasil diupdate.');
        return redirect()->route('kategori-baju.index');
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Edit Kategori Baju') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form wire:submit.prevent="update">
                        <div class="mb-4">
                            <label for="nama_kategori" class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                            <input type="text" id="nama_kategori" wire:model.defer="nama_kategori" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            @error('nama_kategori')
                                <span class="text-red-600 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex justify-end">
                            <a href="{{ route('kategori-baju.index') }}" class="inline-flex items-center px-4 py-2 mr-2 text-xs font-semibold tracking-widest text-gray-700 uppercase bg-gray-200 border border-transparent rounded-md hover:bg-gray-300">Batal</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
