<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

new class extends Component {
    #[Layout('components.layouts.app')]

    public string $name = '';
    public array $selectedPermissions = [];

    public function save()
    {
        $data = $this->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        if (!empty($data['selectedPermissions'])) {
            $role->syncPermissions($data['selectedPermissions']);
        }

        session()->flash('message', __('Role dibuat.'));
        return $this->redirect(route('roles.index'), navigate: true);
    }

    public function with(): array
    {
        return [
            'allPermissions' => Permission::orderBy('name')->pluck('name')->all(),
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Tambah Role') }}</h1>
            <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition" wire:navigate>{{ __('Kembali') }}</a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
            <div class="p-8">
                <form wire:submit="save" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Nama Role') }}</label>
                        <input wire:model="name" type="text" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                        @error('name')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Permissions') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($allPermissions as $perm)
                                <label class="inline-flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-700/40">
                                    <input type="checkbox" value="{{ $perm }}" wire:model="selectedPermissions" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-200">{{ $perm }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedPermissions.*')<p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition">{{ __('Simpan') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
