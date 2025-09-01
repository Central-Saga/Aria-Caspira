<?php // <-- PASTIKAN BARIS INI ADA DI PALING ATAS

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\KategoriBaju;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
new class extends Component {
    use WithPagination;

    // Metode ini dipanggil otomatis untuk mengirim data ke view
    public function with(): array
    {
        return [
            'kategoris' => KategoriBaju::latest()->paginate(5),
        ];
    }

    // Fungsi untuk menghapus data
    public function delete(int $id): void
    {
        $kategori = KategoriBaju::findOrFail($id);
        $kategori->delete();
        session()->flash('success', 'Kategori berhasil dihapus.');
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Manajemen Kategori Baju
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-4">
                        <a href="{{ route('kategori-baju.create') }}" wire:navigate class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700">
                            Tambah Kategori
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="px-4 py-2 mb-4 text-sm text-green-800 bg-green-200 border border-green-300 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">No.</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama Kategori</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($kategoris as $kategori)
                                    <tr wire:key="{{ $kategori->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $kategori->nama_kategori }}</td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <a href="{{ route('kategori-baju.edit', $kategori) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <button wire:click="delete({{ $kategori->id }})" wire:confirm="Anda yakin?" class="ml-4 text-red-600 hover:text-red-900">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada data.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $kategoris->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>