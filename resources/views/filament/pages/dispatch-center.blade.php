<x-filament-panels::page>
    @php
        $k = $this->kpis();
        $roster = $this->roster();
        $queue = $this->waitingQueue();
    @endphp

    <style>
        .dc{--dc-card:#fff;--dc-border:rgba(17,24,39,.08);--dc-fg:#111827;--dc-muted:#6b7280;--dc-track:#f3f4f6;}
        .dark .dc{--dc-card:rgba(255,255,255,.05);--dc-border:rgba(255,255,255,.1);--dc-fg:#f9fafb;--dc-muted:#9ca3af;--dc-track:rgba(255,255,255,.08);}
        .dc-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px;}
        .dc-kpi{background:var(--dc-card);border:1px solid var(--dc-border);border-radius:12px;padding:14px 16px;}
        .dc-kpi b{display:block;font-size:1.7rem;font-weight:800;line-height:1.1;color:var(--dc-fg);font-variant-numeric:tabular-nums;}
        .dc-kpi span{font-size:12px;color:var(--dc-muted);}
        .dc-kpi.amber b{color:#d97706;} .dc-kpi.purple b{color:#8863E5;} .dc-kpi.red b{color:#dc2626;} .dc-kpi.green b{color:#16a34a;}
        .dc-cols{display:grid;grid-template-columns:1.4fr 1fr;gap:16px;}
        @media (max-width:1024px){.dc-cols{grid-template-columns:1fr;}}
        .dc-panel{background:var(--dc-card);border:1px solid var(--dc-border);border-radius:14px;overflow:hidden;}
        .dc-head{display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border-bottom:1px solid var(--dc-border);}
        .dc-head h3{font-size:14px;font-weight:700;color:var(--dc-fg);margin:0;}
        .dc-head .c{font-size:12px;color:var(--dc-muted);font-family:ui-monospace,monospace;}
        .dc-row{display:flex;align-items:center;gap:12px;padding:11px 16px;border-bottom:1px solid var(--dc-border);}
        .dc-row:last-child{border-bottom:0;}
        .dot{width:9px;height:9px;border-radius:50%;flex:none;}
        .dot.on{background:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.15);} .dot.off{background:#9ca3af;}
        .dc-name{font-weight:600;font-size:13.5px;color:var(--dc-fg);}
        .dc-sub{font-size:11.5px;color:var(--dc-muted);}
        .dc-util{width:90px;flex:none;}
        .dc-bar{height:7px;border-radius:5px;background:var(--dc-track);overflow:hidden;}
        .dc-bar i{display:block;height:100%;border-radius:5px;background:linear-gradient(90deg,#8863E5,#744fd6);}
        .dc-bar.full i{background:linear-gradient(90deg,#f59e0b,#dc2626);}
        .dc-pillnum{font-family:ui-monospace,monospace;font-size:12px;color:var(--dc-muted);font-variant-numeric:tabular-nums;}
        .dc-empty{padding:26px 16px;text-align:center;color:var(--dc-muted);font-size:13px;}
        .dc-qmeta{flex:1;min-width:0;}
        .dc-age{font-family:ui-monospace,monospace;font-size:11.5px;padding:2px 7px;border-radius:6px;background:rgba(217,119,6,.12);color:#b45309;white-space:nowrap;}
        .dc-assign{border:0;cursor:pointer;background:#8863E5;color:#fff;font-size:12px;font-weight:600;padding:6px 12px;border-radius:8px;white-space:nowrap;}
        .dc-assign:hover{background:#744fd6;}
        .dc-star{color:#f59e0b;font-size:11.5px;}
    </style>

    <div class="dc" wire:poll.15s>
        {{-- KPI row --}}
        <div class="dc-kpis">
            <div class="dc-kpi green"><b>{{ $k['online_workers'] }}</b><span>{{ __('Online workers') }}</span></div>
            <div class="dc-kpi"><b>{{ $k['today_jobs'] }}</b><span>{{ __("Today's jobs") }}</span></div>
            <div class="dc-kpi amber"><b>{{ $k['waiting'] }}</b><span>{{ __('Waiting queue') }}</span></div>
            <div class="dc-kpi purple"><b>{{ $k['offered'] }}</b><span>{{ __('Offered') }}</span></div>
            <div class="dc-kpi"><b>{{ $k['scheduled'] }}</b><span>{{ __('Scheduled') }}</span></div>
            <div class="dc-kpi red"><b>{{ $k['at_risk'] }}</b><span>{{ __('At risk') }}</span></div>
            <div class="dc-kpi green"><b>{{ $k['auto_success'] }}%</b><span>{{ __('Auto-assign success') }}</span></div>
        </div>

        <div class="dc-cols">
            {{-- Worker roster --}}
            <div class="dc-panel">
                <div class="dc-head">
                    <h3>{{ __('Worker roster') }}</h3>
                    <span class="c">{{ $roster->where('online', true)->count() }}/{{ $roster->count() }} {{ __('online') }}</span>
                </div>
                @forelse ($roster as $w)
                    <div class="dc-row">
                        <span class="dot {{ $w['online'] ? 'on' : 'off' }}"></span>
                        <div style="flex:1;min-width:0;">
                            <div class="dc-name">{{ $w['name'] }}
                                @if ($w['rating']) <span class="dc-star">★ {{ number_format($w['rating'], 1) }}</span> @endif
                            </div>
                            <div class="dc-sub">
                                @if ($w['current']) {{ $w['current'] }} · {{ optional($w['current_at'])->format('H:i') }}
                                @else {{ __('Idle') }} @endif
                            </div>
                        </div>
                        <div class="dc-util">
                            <div class="dc-bar {{ $w['utilization'] >= 100 ? 'full' : '' }}">
                                <i style="width:{{ $w['utilization'] }}%"></i>
                            </div>
                            <div class="dc-sub" style="margin-top:3px;text-align:right;">{{ $w['open'] }}/{{ $w['cap'] }} · {{ __(':n today', ['n' => $w['today']]) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="dc-empty">{{ __('No active workers.') }}</div>
                @endforelse
            </div>

            {{-- Waiting queue --}}
            <div class="dc-panel">
                <div class="dc-head">
                    <h3>{{ __('Waiting queue') }}</h3>
                    <span class="c">{{ $queue->count() }}</span>
                </div>
                @forelse ($queue as $a)
                    <div class="dc-row">
                        <div class="dc-qmeta">
                            <div class="dc-name">{{ $a->service_name }}</div>
                            <div class="dc-sub">
                                {{ optional($a->scheduled_at)->format('D d M · H:i') }}
                                · {{ __(':n tries', ['n' => $a->dispatch_attempts]) }}
                            </div>
                        </div>
                        <span class="dc-age">{{ optional($a->created_at)->diffForHumans(null, true) }}</span>
                        <button type="button" class="dc-assign" wire:click="autoAssign({{ $a->id }})"
                            wire:loading.attr="disabled" wire:target="autoAssign({{ $a->id }})">
                            {{ __('Auto-assign') }}
                        </button>
                    </div>
                @empty
                    <div class="dc-empty">{{ __('Nothing waiting — every job has a worker.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
