@php
    $events = \App\Support\AppointmentTimeline::for($getRecord());
@endphp

{{-- Scoped CSS, not utilities: the panel has no compiled theme (see the map
     view), so Tailwind classes written here would render unstyled. --}}
<ol class="vt-timeline">
    @foreach ($events as $event)
        <li @class(['vt-timeline__item', 'vt-timeline__item--planned' => $event['planned']])>
            <span class="vt-timeline__dot" style="--vt-color: var(--{{ $event['color'] }}-500)">
                <x-filament::icon :icon="$event['icon']" class="vt-timeline__icon" />
            </span>

            <div class="vt-timeline__body">
                <div class="vt-timeline__head">
                    <span class="vt-timeline__title">{{ $event['title'] }}</span>
                    <time class="vt-timeline__time" datetime="{{ $event['at']->toIso8601String() }}">
                        {{ $event['at']->translatedFormat('j M Y, H:i') }}
                    </time>
                </div>

                @if (filled($event['detail']))
                    <div class="vt-timeline__detail">{{ $event['detail'] }}</div>
                @endif

                @if (filled($event['changes']))
                    <dl class="vt-timeline__changes">
                        @foreach ($event['changes'] as [$label, $old, $new])
                            <div class="vt-timeline__change">
                                <dt>{{ $label }}</dt>
                                <dd>
                                    @if ($old !== null)
                                        <span class="vt-timeline__old">{{ $old }}</span>
                                        <span class="vt-timeline__arrow" aria-hidden="true">→</span>
                                    @endif
                                    <span class="vt-timeline__new">{{ $new ?? __('Removed') }}</span>
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if (filled($event['actor']))
                    <div class="vt-timeline__actor">
                        <x-filament::icon icon="heroicon-m-user-circle" class="vt-timeline__actor-icon" />
                        {{ __('by :actor', ['actor' => $event['actor']]) }}
                    </div>
                @endif
            </div>
        </li>
    @endforeach
</ol>

@once
    <style>
        .vt-timeline { list-style: none; margin: 0; padding: 0; }
        .vt-timeline__item { position: relative; display: flex; gap: .875rem; padding-bottom: 1.25rem; }
        .vt-timeline__item:last-child { padding-bottom: 0; }
        /* The rail joining one dot to the next. */
        .vt-timeline__item:not(:last-child)::before {
            content: ''; position: absolute; inset-inline-start: .875rem; top: 1.75rem; bottom: 0;
            width: 2px; margin-inline-start: -1px; background: rgb(0 0 0 / .08);
        }
        .vt-timeline__dot {
            flex: none; display: flex; align-items: center; justify-content: center;
            width: 1.75rem; height: 1.75rem; border-radius: 9999px;
            color: var(--vt-color); background: color-mix(in oklab, var(--vt-color) 14%, transparent);
            box-shadow: 0 0 0 1px color-mix(in oklab, var(--vt-color) 30%, transparent);
        }
        .vt-timeline__icon { width: 1rem; height: 1rem; }
        .vt-timeline__body { min-width: 0; flex: 1; padding-top: .25rem; }
        .vt-timeline__head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: .25rem 1rem; }
        .vt-timeline__title { font-size: .875rem; font-weight: 600; color: rgb(17 24 39); }
        .vt-timeline__time { font-size: .8125rem; color: rgb(107 114 128); font-variant-numeric: tabular-nums; }
        .vt-timeline__detail { margin-top: .125rem; font-size: .8125rem; color: rgb(75 85 99); overflow-wrap: anywhere; }
        .vt-timeline__actor { display: inline-flex; align-items: center; gap: .25rem; margin-top: .25rem; font-size: .75rem; font-weight: 500; color: rgb(107 114 128); }
        .vt-timeline__actor-icon { width: .875rem; height: .875rem; }
        .vt-timeline__changes { margin: .375rem 0 0; padding: .5rem .625rem; border-radius: .5rem; background: rgb(249 250 251); border: 1px solid rgb(0 0 0 / .05); display: grid; gap: .25rem; font-size: .8125rem; }
        .vt-timeline__change { display: flex; flex-wrap: wrap; gap: .25rem .5rem; }
        .vt-timeline__change dt { color: rgb(107 114 128); min-width: 7rem; }
        .vt-timeline__change dd { margin: 0; display: flex; flex-wrap: wrap; gap: .375rem; align-items: baseline; }
        .vt-timeline__old { color: rgb(107 114 128); text-decoration: line-through; }
        .vt-timeline__new { color: rgb(17 24 39); font-weight: 500; }
        .vt-timeline__arrow { color: rgb(156 163 175); }
        [dir=rtl] .vt-timeline__arrow { display: inline-block; transform: scaleX(-1); }
        .dark .vt-timeline__changes { background: rgb(255 255 255 / .04); border-color: rgb(255 255 255 / .08); }
        .dark .vt-timeline__new { color: rgb(243 244 246); }
        .dark .vt-timeline__actor, .dark .vt-timeline__old, .dark .vt-timeline__change dt { color: rgb(156 163 175); }
        .vt-timeline__item--planned .vt-timeline__dot { background: transparent; box-shadow: 0 0 0 1.5px var(--vt-color) inset; }
        .dark .vt-timeline__item:not(:last-child)::before { background: rgb(255 255 255 / .1); }
        .dark .vt-timeline__title { color: rgb(243 244 246); }
        .dark .vt-timeline__time { color: rgb(156 163 175); }
        .dark .vt-timeline__detail { color: rgb(209 213 219); }
    </style>
@endonce
