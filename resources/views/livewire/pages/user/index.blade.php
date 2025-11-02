<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;

new class extends Component {
    use WithPagination;

    #[Layout('components.layouts.app')]

    public string $search = '';
    public string $role = '';
    public bool $showDeleteModal = false;
    public ?User $userToDelete = null;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingRole() { $this->resetPage(); }

    public function confirmDelete(int $id): void
    {
        $this->userToDelete = User::find($id);
        if ($this->userToDelete) {
            $this->showDeleteModal = true;
            $this->dispatch('modal-opened');
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }

    public function deleteUser(int $id): void
    {
        if ($u = User::find($id)) {
            $u->delete();
            session()->flash('message', __('User deleted.'));
        }
        $this->cancelDelete();
    }

    public function with(): array
    {
        $q = User::query();
        if ($this->search !== '') {
            $s = "%{$this->search}%";
            $q->where(function ($x) use ($s) {
                $x->where('name','like',$s)->orWhere('email','like',$s);
            });
        }
        if (in_array($this->role, ['Super Admin','Admin','Staff'])) {
            $q->where('role', $this->role);
        }

        return [
            'items' => $q->orderBy('name')->paginate(10),
            'stats' => [
                'total' => User::count(),
                'admins' => User::where('role','Admin')->count(),
                'staffs' => User::where('role','Staff')->count(),
            ],
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Pengguna') }}</h1>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">{{ __('Kelola akun pengguna dan perannya') }}</p>
                </div>
                <a href="{{ route('users.create') }}" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Tambah Pengguna') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6"><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total') }}</p><p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total'] }}</p></div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6"><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Admin') }}</p><p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['admins'] }}</p></div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6"><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Staff') }}</p><p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['staffs'] }}</p></div>
        </div>

        <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative max-w-md">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
                <input wire:model.live="search" type="text" class="block w-full pl-12 pr-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-lg" placeholder="{{ __('Cari nama atau email...') }}">
            </div>
            <div>
                <select wire:model.live="role" class="block w-full px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg">
                    <option value="">{{ __('Semua Peran') }}</option>
                    <option>Super Admin</option>
                    <option>Admin</option>
                    <option>Staff</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Nama') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Peran') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($items as $u)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $u->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $u->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">{{ $u->role }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('users.edit', $u) }}" class="text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white mr-3">{{ __('Edit') }}</a>
                                <button wire:click="confirmDelete({{ $u->id }})" class="text-red-600 hover:text-red-700">{{ __('Hapus') }}</button>
                            </td>
                        </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tidak ada pengguna') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())<div class="px-6 py-4">{{ $items->links() }}</div>@endif
        </div>

        <div style="display: {{ $showDeleteModal ? 'block' : 'none' }};">
            <div class="fixed inset-0 backdrop-blur-md transition-opacity z-50" wire:click="cancelDelete"></div>
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('Hapus Pengguna') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">{{ __('Tindakan ini tidak dapat dibatalkan.') }}</p>
                        <div class="sm:flex sm:flex-row-reverse gap-3">
                            <button wire:click="deleteUser({{ $userToDelete ? $userToDelete->id : 0 }})" class="inline-flex justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500">{{ __('Hapus') }}</button>
                            <button wire:click="cancelDelete" class="inline-flex justify-center rounded-xl bg-gray-100 dark:bg-gray-700 px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">{{ __('Batal') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

