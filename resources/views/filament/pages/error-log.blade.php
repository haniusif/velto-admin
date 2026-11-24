<x-filament-panels::page>
    {{-- Hand-rolled styling, like Admin Alerts / Dispatch Settings: the panel
         ships no compiled theme, so utility classes would not render. --}}
    <style>
        .el{--el-card:#fff;--el-border:rgba(17,24,39,.08);--el-fg:#111827;--el-muted:#6b7280;--el-input:#fff;--el-row:rgba(17,24,39,.025);--el-code:#f6f7f9;}
        .dark .el{--el-card:rgba(255,255,255,.05);--el-border:rgba(255,255,255,.1);--el-fg:#f9fafb;--el-muted:#9ca3af;--el-input:rgba(255,255,255,.06);--el-row:rgba(255,255,255,.03);--el-code:rgba(0,0,0,.25);}
        .el-bar{display:flex;flex-wrap:wrap;gap:14px;align-items:end;background:var(--el-card);border:1px solid var(--el-border);border-radius:14px;padding:16px 18px;margin-bottom:16px;}
        .el-f{display:flex;flex-direction:column;gap:5px;}
        .el-f label{font-size:12px;font-weight:600;color:var(--el-fg);}
        .el-f select{padding:8px 11px;border-radius:9px;border:1px solid var(--el-border);background:var(--el-input);color:var(--el-fg);font-size:13.5px;min-width:200px;}
        .el-f select:focus{outline:2px solid #8863E5;outline-offset:0;border-color:#8863E5;}
        .el-count{margin-inline-start:auto;font-size:12.5px;color:var(--el-muted);align-self:center;}
        .el-empty{background:var(--el-card);border:1px solid var(--el-border);border-radius:14px;padding:40px 20px;text-align:center;color:var(--el-muted);font-size:14px;}
        .el-list{display:flex;flex-direction:column;gap:8px;}
        .el-item{background:var(--el-card);border:1px solid var(--el-border);border-radius:12px;overflow:hidden;}
        .el-head{list-style:none;cursor:pointer;display:flex;gap:12px;align-items:flex-start;padding:11px 14px;}
        .el-head::-webkit-details-marker{display:none;}
        .el-item[open] .el-head{border-bottom:1px solid var(--el-border);}
        .el-badge{flex:none;font-size:10.5px;font-weight:700;letter-spacing:.03em;padding:3px 8px;border-radius:999px;text-transform:uppercase;}
        .el-badge.danger{background:rgba(239,68,68,.12);color:#dc2626;}
        .el-badge.warning{background:rgba(245,158,11,.14);color:#b45309;}
        .el-badge.info{background:rgba(59,130,246,.12);color:#2563eb;}
        .el-badge.gray{background:rgba(107,114,128,.14);color:#6b7280;}
        .dark .el-badge.danger{color:#f87171;}
        .dark .el-badge.warning{color:#fbbf24;}
        .dark .el-badge.info{color:#60a5fa;}
        .el-time{flex:none;font-size:12px;color:var(--el-muted);font-family:ui-monospace,monospace;padding-top:1px;}
        .el-msg{flex:1;min-width:0;font-size:13px;color:var(--el-fg);line-height:1.45;word-break:break-word;direction:ltr;text-align:start;}
        .el-detail{margin:0;padding:12px 14px;background:var(--el-code);font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11.5px;line-height:1.55;color:var(--el-fg);white-space:pre-wrap;word-break:break-word;direction:ltr;text-align:start;max-height:460px;overflow:auto;}
        .el-nodetail{padding:10px 14px;font-size:12px;color:var(--el-muted);}
        .el-note{display:flex;gap:8px;align-items:flex-start;font-size:12px;color:var(--el-muted);margin-bottom:12px;}
    </style>

    @php($entries = $this->entries())

    <div class="el">
        <div class="el-note">
            ℹ {{ __('Timestamps are shown exactly as written in the log file (server time).') }}
        </div>

        <div class="el-bar">
            <div class="el-f">
                <label>{{ __('Log file') }}</label>
                <select wire:model.live="file">
                    @forelse ($this->files() as $name => $label)
                        <option value="{{ $name }}">{{ $label }}</option>
                    @empty
                        <option value="">{{ __('No log files') }}</option>
                    @endforelse
                </select>
            </div>

            <div class="el-f">
                <label>{{ __('Level') }}</label>
                <select wire:model.live="level">
                    @foreach ($this->levelOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="el-count">
                {{ __(':count entries shown', ['count' => count($entries)]) }}
            </div>
        </div>

        @if (count($entries) === 0)
            <div class="el-empty">
                {{ __('Nothing to show — no matching entries in this file. 🎉') }}
            </div>
        @else
            <div class="el-list">
                @foreach ($entries as $e)
                    <details class="el-item">
                        <summary class="el-head">
                            <span class="el-badge {{ $e['color'] }}">{{ $e['level'] }}</span>
                            <span class="el-time">{{ $e['time'] }}</span>
                            <span class="el-msg">{{ $e['message'] }}</span>
                        </summary>
                        @if ($e['detail'] !== '')
                            <pre class="el-detail">{{ $e['detail'] }}</pre>
                        @else
                            <div class="el-nodetail">{{ __('No stack trace or context.') }}</div>
                        @endif
                    </details>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
