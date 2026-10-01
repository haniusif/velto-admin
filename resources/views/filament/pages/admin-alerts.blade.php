<x-filament-panels::page>
    {{-- Same hand-rolled look as Dispatch Settings: the panel has no compiled
         theme, so utility classes would not render. --}}
    <style>
        .aa{--aa-card:#fff;--aa-border:rgba(17,24,39,.08);--aa-fg:#111827;--aa-muted:#6b7280;--aa-input:#fff;--aa-row:rgba(17,24,39,.025);}
        .dark .aa{--aa-card:rgba(255,255,255,.05);--aa-border:rgba(255,255,255,.1);--aa-fg:#f9fafb;--aa-muted:#9ca3af;--aa-input:rgba(255,255,255,.06);--aa-row:rgba(255,255,255,.03);}
        .aa-sec{background:var(--aa-card);border:1px solid var(--aa-border);border-radius:14px;padding:18px 20px;margin-bottom:16px;}
        .aa-sec h3{font-size:14px;font-weight:700;color:var(--aa-fg);margin:0 0 3px;}
        .aa-sec p{font-size:12.5px;color:var(--aa-muted);margin:0 0 14px;}
        .aa-warn{display:flex;gap:8px;align-items:flex-start;font-size:12.5px;color:#b45309;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:9px 12px;margin-bottom:10px;}
        .aa-table{width:100%;border-collapse:collapse;font-size:13.5px;color:var(--aa-fg);}
        .aa-table th{font-size:12px;font-weight:600;color:var(--aa-muted);text-align:start;padding:8px 10px;border-bottom:1px solid var(--aa-border);}
        .aa-table th.c,.aa-table td.c{text-align:center;width:90px;}
        .aa-table td{padding:10px;border-bottom:1px solid var(--aa-border);}
        .aa-table tr:nth-child(even) td{background:var(--aa-row);}
        .aa-table input[type=checkbox]{width:17px;height:17px;accent-color:#8863E5;cursor:pointer;}
        .aa-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;}
        .aa-f label{display:block;font-size:12px;font-weight:600;color:var(--aa-fg);margin-bottom:5px;}
        .aa-f .hint{display:block;font-size:11.5px;color:var(--aa-muted);font-weight:400;margin-top:4px;}
        .aa-f textarea,.aa-f select{width:100%;padding:8px 11px;border-radius:9px;border:1px solid var(--aa-border);background:var(--aa-input);color:var(--aa-fg);font-size:13.5px;}
        .aa-f textarea{min-height:78px;resize:vertical;direction:ltr;font-family:ui-monospace,monospace;}
        .aa-f textarea:focus,.aa-f select:focus{outline:2px solid #8863E5;outline-offset:0;border-color:#8863E5;}
        .aa-actions{display:flex;flex-wrap:wrap;gap:10px;}
        .aa-btn{background:#8863E5;color:#fff;border:0;border-radius:10px;padding:11px 22px;font-weight:650;font-size:14px;cursor:pointer;}
        .aa-btn:hover{background:#744fd6;}
        .aa-btn.ghost{background:transparent;color:var(--aa-fg);border:1px solid var(--aa-border);}
        .aa-btn.ghost:hover{background:var(--aa-row);}
        .aa-scroll{overflow-x:auto;}
    </style>

    <div class="aa">
        <div class="aa-sec">
            <h3>{{ __('Recipients') }}</h3>
            <p>{{ __('The bell reaches every admin user. Email and SMS go to the lists below.') }}</p>

            @unless ($this->mailReady())
                <div class="aa-warn">⚠ {{ __('Email is not set up on the server yet — emails are only written to the log. Add the mailbox details to .env (MAIL_*).') }}</div>
            @endunless
            @unless ($this->smsReady())
                <div class="aa-warn">⚠ {{ __('SMS is not set up on the server (4jawaly keys missing) — no SMS will be sent.') }}</div>
            @endunless

            <div class="aa-grid">
                <div class="aa-f">
                    <label for="aa-emails">{{ __('Email addresses') }}</label>
                    <textarea id="aa-emails" wire:model="emails" placeholder="ops@velto.sa, manager@velto.sa"></textarea>
                    <span class="hint">{{ __('Separate with commas or new lines.') }}</span>
                </div>
                <div class="aa-f">
                    <label for="aa-phones">{{ __('Phone numbers (SMS)') }}</label>
                    <textarea id="aa-phones" wire:model="phones" placeholder="966500000000, 966511111111"></textarea>
                    <span class="hint">{{ __('Full number with country code. Each SMS uses 4jawaly credit.') }}</span>
                </div>
                <div class="aa-f">
                    <label for="aa-lang">{{ __('Alert language') }}</label>
                    <select id="aa-lang" wire:model="language">
                        <option value="ar">العربية</option>
                        <option value="en">English</option>
                    </select>
                    <span class="hint">{{ __('Arabic SMS fit fewer characters per message.') }}</span>
                </div>
            </div>
        </div>

        <div class="aa-sec">
            <h3>{{ __('What to send, and where') }}</h3>
            <p>{{ __('Tick a channel for each event. The admin who made a change does not get a bell for it.') }}</p>

            <div class="aa-scroll">
                <table class="aa-table">
                    <thead>
                        <tr>
                            <th>{{ __('Event') }}</th>
                            <th class="c">{{ __('Bell') }}</th>
                            <th class="c">{{ __('Email') }}</th>
                            <th class="c">SMS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Services\Notifications\AdminAlertSettings::EVENTS as $event => $_)
                            <tr>
                                <td>{{ __(\App\Services\Notifications\AdminAlertMessages::LABELS[$event]) }}</td>
                                @foreach (\App\Services\Notifications\AdminAlertSettings::CHANNELS as $channel)
                                    <td class="c">
                                        <input type="checkbox" wire:model="matrix.{{ $event }}.{{ $channel }}"
                                               aria-label="{{ __(\App\Services\Notifications\AdminAlertMessages::LABELS[$event]) }} — {{ $channel }}">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="aa-actions">
            <button type="button" class="aa-btn" wire:click="save" wire:loading.attr="disabled">{{ __('Save') }}</button>
            <button type="button" class="aa-btn ghost" wire:click="sendTest" wire:loading.attr="disabled">{{ __('Send a test alert') }}</button>
        </div>
    </div>
</x-filament-panels::page>
