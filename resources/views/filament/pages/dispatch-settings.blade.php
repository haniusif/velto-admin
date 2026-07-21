<x-filament-panels::page>
    <style>
        .ds{--ds-card:#fff;--ds-border:rgba(17,24,39,.08);--ds-fg:#111827;--ds-muted:#6b7280;--ds-input:#fff;}
        .dark .ds{--ds-card:rgba(255,255,255,.05);--ds-border:rgba(255,255,255,.1);--ds-fg:#f9fafb;--ds-muted:#9ca3af;--ds-input:rgba(255,255,255,.06);}
        .ds-sec{background:var(--ds-card);border:1px solid var(--ds-border);border-radius:14px;padding:18px 20px;margin-bottom:16px;}
        .ds-sec h3{font-size:14px;font-weight:700;color:var(--ds-fg);margin:0 0 3px;}
        .ds-sec p{font-size:12.5px;color:var(--ds-muted);margin:0 0 14px;}
        .ds-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;}
        .ds-f label{display:block;font-size:12px;font-weight:600;color:var(--ds-fg);margin-bottom:5px;}
        .ds-f .hint{font-size:11px;color:var(--ds-muted);font-weight:400;}
        .ds-f input[type=number],.ds-f select{width:100%;padding:8px 11px;border-radius:9px;border:1px solid var(--ds-border);
            background:var(--ds-input);color:var(--ds-fg);font-size:13.5px;font-variant-numeric:tabular-nums;}
        .ds-f input:focus,.ds-f select:focus{outline:2px solid #8863E5;outline-offset:0;border-color:#8863E5;}
        .ds-toggle{display:flex;align-items:center;gap:9px;font-size:13px;color:var(--ds-fg);padding-top:22px;}
        .ds-weights{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;}
        .ds-total{margin-top:12px;font-size:13px;font-family:ui-monospace,monospace;color:var(--ds-muted);}
        .ds-total b{color:var(--ds-fg);}
        .ds-total .ok{color:#16a34a;} .ds-total .off{color:#d97706;}
        .ds-save{margin-top:4px;}
        .ds-btn{background:#8863E5;color:#fff;border:0;border-radius:10px;padding:11px 22px;font-weight:650;font-size:14px;cursor:pointer;}
        .ds-btn:hover{background:#744fd6;}
    </style>

    <div class="ds">
        {{-- Mode & strategy --}}
        <div class="ds-sec">
            <h3>{{ __('Mode & strategy') }}</h3>
            <p>{{ __('How the engine assigns, and which strategy it uses by default.') }}</p>
            <div class="ds-grid">
                <div class="ds-f">
                    <label>{{ __('Assignment mode') }}</label>
                    <select wire:model="mode">
                        <option value="direct">{{ __('Direct (assign immediately)') }}</option>
                        <option value="offer">{{ __('Offer (worker accepts)') }}</option>
                    </select>
                </div>
                <div class="ds-f">
                    <label>{{ __('Default strategy') }}</label>
                    <select wire:model="default_strategy">
                        @foreach ($this::STRATEGIES as $s)
                            <option value="{{ $s }}">{{ __(ucwords(str_replace('_', ' ', $s))) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ds-f"><div class="ds-toggle"><input type="checkbox" wire:model="auto_dispatch_enabled"> {{ __('Auto-dispatch enabled') }}</div></div>
                <div class="ds-f"><div class="ds-toggle"><input type="checkbox" wire:model="require_online"> {{ __('Require workers online') }}</div></div>
            </div>
        </div>

        {{-- Timing --}}
        <div class="ds-sec">
            <h3>{{ __('Timing & retries') }}</h3>
            <p>{{ __('When scheduled jobs dispatch, how long offers live, and retry behaviour.') }}</p>
            <div class="ds-grid">
                <div class="ds-f"><label>{{ __('Immediate threshold') }} <span class="hint">{{ __('min') }}</span></label><input type="number" wire:model="immediate_threshold_minutes"></div>
                <div class="ds-f"><label>{{ __('Dispatch lead') }} <span class="hint">{{ __('min') }}</span></label><input type="number" wire:model="dispatch_lead_minutes"></div>
                <div class="ds-f"><label>{{ __('Acceptance timeout') }} <span class="hint">s</span></label><input type="number" wire:model="acceptance_timeout_seconds"></div>
                <div class="ds-f"><label>{{ __('Retry interval') }} <span class="hint">s</span></label><input type="number" wire:model="retry_interval_seconds"></div>
                <div class="ds-f"><label>{{ __('Max retries') }}</label><input type="number" wire:model="max_retries"></div>
                <div class="ds-f"><label>{{ __('Distance radius') }} <span class="hint">km</span></label><input type="number" wire:model="distance_radius_km"></div>
            </div>
        </div>

        {{-- Scoring weights --}}
        <div class="ds-sec">
            <h3>{{ __('Scoring weights') }}</h3>
            <p>{{ __('Relative importance of each factor for the auto strategy. Values are normalized, so they need not sum to 1.') }}</p>
            <div class="ds-weights">
                @foreach ($this::WEIGHT_KEYS as $k)
                    <div class="ds-f">
                        <label>{{ __(ucwords(str_replace('_', ' ', $k))) }}</label>
                        <input type="number" step="0.05" min="0" max="1" wire:model.live="weights.{{ $k }}">
                    </div>
                @endforeach
            </div>
            @php $t = $this->weightTotal(); @endphp
            <div class="ds-total">{{ __('Total') }}: <b class="{{ abs($t - 1) < 0.001 ? 'ok' : 'off' }}">{{ number_format($t, 2) }}</b>
                <span class="hint">({{ __('normalized on use') }})</span></div>
        </div>

        <div class="ds-save">
            <button type="button" class="ds-btn" wire:click="save" wire:loading.attr="disabled">
                {{ __('Save settings') }}
            </button>
        </div>
    </div>
</x-filament-panels::page>
