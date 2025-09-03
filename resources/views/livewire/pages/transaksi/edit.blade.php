<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\Transaksi;
use App\Models\Baju;

new class extends Component {
    #[Layout('components.layouts.app')]

    public Transaksi $transaksi;
    public int $baju_id;
    public string $jenis_transaksi = 'masuk';
    public int $jumlah = 1;
    public string $tanggal;
    public ?string $keterangan = null;

    public function mount(Transaksi $transaksi)
    {
        $this->transaksi = $transaksi;
        $this->baju_id = $transaksi->baju_id;
        $this->jenis_transaksi = $transaksi->jenis_transaksi;
        $this->jumlah = (int)$transaksi->jumlah;
        $this->tanggal = $transaksi->tanggal?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
        $this->keterangan = $transaksi->keterangan;
    }

    public function update()
    {
        $validated = $this->validate([
            'baju_id' => 'required|exists:bajus,id',
            'jenis_transaksi' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $before = $this->transaksi->replicate();

            // Revert stok dari transaksi lama
            $oldBaju = Baju::lockForUpdate()->find($before->baju_id);
            if ($oldBaju) {
                $oldDelta = $before->jenis_transaksi === 'masuk' ? -$before->jumlah : $before->jumlah;
                $newStock = $oldBaju->stok_tersedia + $oldDelta;
                if ($newStock < 0) $newStock = 0; // Clamp to zero
                $oldBaju->update(['stok_tersedia' => $newStock]);
            }

            // Apply stok untuk transaksi baru
            $newBaju = Baju::lockForUpdate()->findOrFail($validated['baju_id']);
            $apply = $validated['jenis_transaksi'] === 'masuk' ? $validated['jumlah'] : -$validated['jumlah'];
            if ($validated['jenis_transaksi'] === 'keluar' && ($newBaju->stok_tersedia + $apply) < 0) {
                throw new \Exception(__('Stok tidak mencukupi.'));
            }
            $newBaju->increment('stok_tersedia', $apply);

            $this->transaksi->update($validated);
        });

        session()->flash('message', __('Transaksi berhasil diperbarui.'));
        return $this->redirect(route('inventory.transaksi'), navigate: true);
    }

    public function with(): array
    {
        return [
            'items' => Baju::orderBy('nama_baju')->get(),
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Edit Transaksi') }}</h1>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">{{ __('Perbarui catatan transaksi stok') }}</p>
                </div>
                <a href="{{ route('inventory.transaksi') }}" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200" wire:navigate>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    {{ __('Kembali ke Transaksi') }}
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
            <div class="p-8">
                <form wire:submit="update" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Produk (Baju)') }}</label>
                        <select wire:model="baju_id" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach($items as $b)
                                <option value="{{ $b->id }}">{{ $b->nama_baju }} — Stok: {{ $b->stok_tersedia }}</option>
                            @endforeach
                        </select>
                        @error('baju_id')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Jenis Transaksi') }}</label>
                            <select wire:model="jenis_transaksi" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="masuk">{{ __('Masuk (+)') }}</option>
                                <option value="keluar">{{ __('Keluar (−)') }}</option>
                            </select>
                            @error('jenis_transaksi')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Jumlah') }}</label>
                            <input wire:model="jumlah" type="number" min="1" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('jumlah')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Tanggal & Waktu') }}</label>
                            <input wire:model="tanggal" type="datetime-local" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('tanggal')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Keterangan') }}</label>
                        <input wire:model="keterangan" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="restock / penjualan / retur ...">
                        @error('keterangan')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('inventory.transaksi') }}" class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200" wire:navigate>{{ __('Batal') }}</a>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <div class="flex items-center space-x-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ __('Simpan Perubahan') }}</span>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

