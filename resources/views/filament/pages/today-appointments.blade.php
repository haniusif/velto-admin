@php
    $summary = $this->summary();
@endphp

<x-filament-panels::page>
    {{-- The shift's shape at a glance, before reading any rows. --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @php
            $tiles = [
                ['label' => __('Bookings'), 'value' => $summary['total'], 'tone' => 'text-gray-900 dark:text-white'],
                ['label' => __('Upcoming'), 'value' => $summary['upcoming'], 'tone' => 'text-primary-600 dark:text-primary-400'],
                ['label' => __('In progress'), 'value' => $summary['in_progress'], 'tone' => 'text-primary-600 dark:text-primary-400'],
                ['label' => __('Completed'), 'value' => $summary['completed'], 'tone' => 'text-success-600 dark:text-success-400'],
                ['label' => __('Cancelled'), 'value' => $summary['cancelled'], 'tone' => 'text-danger-600 dark:text-danger-400'],
            ];
        @endphp

        @foreach ($tiles as $tile)
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                <div class="text-2xl font-bold {{ $tile['tone'] }}">{{ $tile['value'] }}</div>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $tile['label'] }}</div>
            </div>
        @endforeach

        {{-- Unassigned earns its own treatment: it is the only tile that means
             somebody has to do something right now. --}}
        <div @class([
            'rounded-xl border p-4',
            'border-danger-300 bg-danger-50 dark:border-danger-500/40 dark:bg-danger-500/10' => $summary['unassigned'] > 0,
            'border-gray-200 bg-white dark:border-white/10 dark:bg-white/5' => $summary['unassigned'] === 0,
        ])>
            <div @class([
                'text-2xl font-bold',
                'text-danger-600 dark:text-danger-400' => $summary['unassigned'] > 0,
                'text-gray-900 dark:text-white' => $summary['unassigned'] === 0,
            ])>{{ $summary['unassigned'] }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Unassigned') }}</div>
        </div>
    </div>

    @if ($summary['completed'] > 0)
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Earned today') }}:
            <span class="font-semibold text-gray-900 dark:text-white">
                {{ \Filament\Support\format_money($summary['revenue'], 'SAR') }}
            </span>
            <span class="text-gray-400">({{ __('completed visits only') }})</span>
        </div>
    @endif

    {{ $this->table }}
</x-filament-panels::page>
