<x-filament-panels::page>
    @php
        $board = $this->board();
        $meta = [
            'waiting'        => ['label' => __('Waiting'),        'color' => '#d97706'],
            'scheduled'      => ['label' => __('Scheduled'),      'color' => '#64748b'],
            'auto_assigning' => ['label' => __('Auto-assigning'), 'color' => '#6d8bff'],
            'offered'        => ['label' => __('Offered'),        'color' => '#8863E5'],
            'assigned'       => ['label' => __('Assigned'),       'color' => '#2fa27e'],
            'en_route'       => ['label' => __('En route'),       'color' => '#3ba7d9'],
            'working'        => ['label' => __('Working'),        'color' => '#6d5ae0'],
        ];
        $isAr = str_starts_with(app()->getLocale(), 'ar');
    @endphp

    <style>
        .db{--db-card:#fff;--db-col:#f8f7fc;--db-border:rgba(17,24,39,.08);--db-fg:#111827;--db-muted:#6b7280;}
        .dark .db{--db-card:rgba(255,255,255,.06);--db-col:rgba(255,255,255,.03);--db-border:rgba(255,255,255,.1);--db-fg:#f9fafb;--db-muted:#9ca3af;}
        .db-scroll{display:grid;grid-auto-flow:column;grid-auto-columns:minmax(210px,1fr);gap:12px;overflow-x:auto;padding-bottom:10px;}
        .db-col{background:var(--db-col);border:1px solid var(--db-border);border-radius:12px;padding:10px;min-height:160px;}
        .db-col-h{display:flex;align-items:center;gap:8px;margin-bottom:10px;padding:2px 4px;}
        .db-col-h .lbl{font-size:12.5px;font-weight:700;color:var(--db-fg);}
        .db-col-h .cnt{margin-inline-start:auto;font-family:ui-monospace,monospace;font-size:11.5px;color:var(--db-muted);}
        .db-dot{width:9px;height:9px;border-radius:3px;flex:none;}
        .db-card{background:var(--db-card);border:1px solid var(--db-border);border-inline-start:3px solid var(--_c);border-radius:9px;padding:9px 11px;margin-bottom:8px;}
        .db-card .svc{font-size:12.5px;font-weight:650;color:var(--db-fg);}
        .db-card .meta{font-size:11px;color:var(--db-muted);margin-top:2px;}
        .db-card .wk{display:inline-flex;align-items:center;gap:4px;margin-top:5px;font-size:11px;color:var(--db-fg);}
        .db-empty{font-size:12px;color:var(--db-muted);text-align:center;padding:16px 6px;opacity:.7;}
    </style>

    <div class="db" wire:poll.15s>
        <div class="db-scroll">
            @foreach ($meta as $state => $m)
                @php $items = $board[$state] ?? collect(); @endphp
                <div class="db-col">
                    <div class="db-col-h">
                        <span class="db-dot" style="background:{{ $m['color'] }}"></span>
                        <span class="lbl">{{ $m['label'] }}</span>
                        <span class="cnt">{{ $items->count() }}</span>
                    </div>
                    @forelse ($items as $a)
                        <div class="db-card" style="--_c:{{ $m['color'] }}">
                            <div class="svc">{{ $isAr && $a->service_name_ar ? $a->service_name_ar : $a->service_name }}</div>
                            <div class="meta">
                                {{ optional($a->scheduled_at)->format('D H:i') }}
                                @if ($a->area_id) · #{{ $a->id }} @endif
                            </div>
                            @if ($a->worker)
                                <div class="wk">
                                    <x-heroicon-o-user class="w-3 h-3" />
                                    {{ $a->worker->name }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="db-empty">—</div>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
