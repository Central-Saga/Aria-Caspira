<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    #[Layout('components.layouts.app')]

    public string $search = '';
    public string $logName = '';
    public string $event = '';
    public ?int $causer_id = null;
    public ?string $fromDate = null; // Y-m-d
    public ?string $toDate = null;   // Y-m-d

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingLogName(): void { $this->resetPage(); }
    public function updatingEvent(): void { $this->resetPage(); }
    public function updatingCauserId(): void { $this->resetPage(); }
    public function updatedFromDate(): void { $this->resetPage(); }
    public function updatedToDate(): void { $this->resetPage(); }

    public function with(): array
    {
        $query = Activity::query()->with(['causer']);

        if ($this->search !== '') {
            $s = "%{$this->search}%";
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', $s)
                  ->orWhere('log_name', 'like', $s)
                  ->orWhere('event', 'like', $s);
            });
        }
        if ($this->logName !== '') {
            $query->where('log_name', $this->logName);
        }
        if ($this->event !== '') {
            $query->where('event', $this->event);
        }
        if (!empty($this->causer_id)) {
            $query->where('causer_id', $this->causer_id);
        }
        if ($this->fromDate || $this->toDate) {
            $from = $this->fromDate ? \Carbon\Carbon::parse($this->fromDate)->startOfDay() : Activity::min('created_at');
            $to = $this->toDate ? \Carbon\Carbon::parse($this->toDate)->endOfDay() : now();
            if ($from && $to) {
                $query->whereBetween('created_at', [$from, $to]);
            }
        }

        $logNames = Activity::query()->select('log_name')->distinct()->pluck('log_name')->filter()->values();
        $events = Activity::query()->select('event')->distinct()->pluck('event')->filter()->values();
        $causers = User::query()
            ->whereIn('id', Activity::query()->whereNotNull('causer_id')->select('causer_id')->distinct())
            ->orderBy('name')
            ->get(['id','name']);

        return [
            'items' => $query->orderByDesc('created_at')->paginate(15),
            'filters' => [
                'log_names' => $logNames,
                'events' => $events,
                'causers' => $causers,
            ],
            'stats' => [
                'total' => Activity::count(),
                'today' => Activity::whereDate('created_at', today())->count(),
            ],
        ];
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">{{ __('Activity Logs') }}</h1>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-300">{{ __('Audit trail dari perubahan data dan aktivitas pengguna') }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Total Logs') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Hari Ini') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['today'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Filter Aktif') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ ($logName || $event || $causer_id || $fromDate || $toDate || $search) ? 'Ya' : 'Tidak' }}</p>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 lg:grid-cols-5 gap-4">
            <div class="relative lg:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live="search" type="text" class="block w-full pl-12 pr-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:outline-none shadow-lg" placeholder="{{ __('Cari deskripsi/event/log name...') }}">
            </div>
            <div>
                <select wire:model.live="logName" class="block w-full px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg">
                    <option value="">{{ __('Semua Log') }}</option>
                    @foreach($filters['log_names'] as $ln)
                        <option value="{{ $ln }}">{{ $ln }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="event" class="block w-full px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg">
                    <option value="">{{ __('Semua Event') }}</option>
                    @foreach($filters['events'] as $ev)
                        <option value="{{ $ev }}">{{ $ev }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="causer_id" class="block w-full px-4 py-3 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow-lg">
                    <option value="">{{ __('Semua Pengguna') }}</option>
                    @foreach($filters['causers'] as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-2">{{ __('Dari Tanggal') }}</label>
                <input wire:model="fromDate" type="date" class="block w-full px-4 py-2.5 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow" />
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-2">{{ __('Sampai Tanggal') }}</label>
                <input wire:model="toDate" type="date" class="block w-full px-4 py-2.5 border-0 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 shadow" />
            </div>
            <div class="flex items-end">
                <button type="button" wire:click="$set('fromDate', null); $set('toDate', null)" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">{{ __('Reset Tanggal') }}</button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/30">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Waktu') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Log') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Event') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Deskripsi') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Causer') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Subject') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($items as $a)
                            <tr class="align-top">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $a->created_at?->format('d M Y H:i:s') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $a->log_name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ $a->event ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200 max-w-xl">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $a->description }}</div>
                                    @if(!empty($a->properties))
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ Str::limit(json_encode($a->properties), 120) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">{{ optional($a->causer)->name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                    {{ class_basename($a->subject_type) }}#{{ $a->subject_id ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">{{ __('Tidak ada log') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
                <div class="px-6 py-4">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
    
</div>
