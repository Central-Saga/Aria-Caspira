<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;

new class extends Component {
    use WithPagination;
    #[Layout('components.layouts.app')]

    public string $search = '';

    public function updatingSearch() { $this->resetPage(); }

    public function with(): array
    {
        $q = Role::query();
        if ($this->search !== '') {
            $q->where('name','like',"%{$this->search}%");
        }
        return [
            'items' => $q->orderBy('name')->paginate(10),
            'stats' => [ 'total' => Role::count() ],
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Roles') }}</h1>
                <p class="text-gray-600 dark:text-gray-300 mt-2">{{ __('Kelola daftar role aplikasi') }}</p>
            </div>
            <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition">{{ __('Tambah Role') }}</a>
        </div>

        <div class="mb-6">
            <input wire:model.live="search" type="text" class="block w-full max-w-md px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg" placeholder="{{ __('Cari role...') }}">
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/30">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Nama Role') }}</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($items as $r)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $r->name }}</td>
                            <td class="px-6 py-4 text-right text-sm"><a href="{{ route('roles.edit', $r) }}" class="text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white">{{ __('Edit') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tidak ada role') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($items->hasPages())<div class="px-6 py-4">{{ $items->links() }}</div>@endif
        </div>
    </div>
</div>

