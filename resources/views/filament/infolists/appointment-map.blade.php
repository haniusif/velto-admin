@php
    $record = $getRecord();

    // Cast through float so nothing but a number reaches the URLs below, and
    // so a stored 0,0 counts as missing — that point is the Gulf of Guinea,
    // not Riyadh, and drawing it would look like a real answer.
    $lat = is_numeric($record->latitude) ? (float) $record->latitude : null;
    $lng = is_numeric($record->longitude) ? (float) $record->longitude : null;
    $hasPoint = $lat !== null && $lng !== null && ($lat !== 0.0 || $lng !== 0.0);

    $key = config('services.google_maps.key');
    $locale = app()->getLocale();

    if ($hasPoint) {
        $point = $lat.','.$lng;
        $coords = $lat.', '.$lng;
        $googleUrl = 'https://www.google.com/maps/search/?api=1&query='.$point;
        $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination='.$point;

        // Drawn underneath the live map and revealed whenever the JavaScript
        // API does not come up — a wrong key, an unbilled project or a blocked
        // script otherwise leaves a grey rectangle whose only explanation is
        // in the browser console. Static Maps is separately authorised on this
        // key, so the picture survives the interactive map failing.
        $staticMap = filled($key)
            ? 'https://maps.googleapis.com/maps/api/staticmap?'.http_build_query([
                'center' => $point,
                'zoom' => 16,
                'size' => '640x320',
                'scale' => 2,
                'maptype' => 'roadmap',
                'language' => $locale,
                'region' => 'SA',
                'markers' => 'color:0x8863E5|'.$point,
                'key' => $key,
            ])
            : null;
    }
@endphp

@if ($hasPoint)
    <div
        x-data="{
            live: false,
            // The static image is a separate Google API and may not be
            // enabled on the same key — it answers 403 on production today,
            // which renders as a torn-image icon unless caught.
            pictureFailed: false,
            copied: false,

            boot() {
                const key = @js($key);
                if (! key) return;

                // Every window assignment below is wrapped: a privacy or
                // script-blocking extension can make writing to window throw
                // (Firefox reports it as an XrayWrapper cross-origin error),
                // and an exception here would abort boot() and leave the map
                // permanently unloaded rather than falling back to the picture.
                try {
                    // Google calls this globally when the key is rejected,
                    // which can happen after the map object was already built.
                    window.gm_authFailure = () => { this.live = false };
                } catch (e) {}

                if (window.google?.maps) { this.draw(); return }

                let loader;
                try {
                    loader = window.veltoMapsLoader;
                } catch (e) {}

                if (! loader) {
                    loader = new Promise((resolve, reject) => {
                        const script = document.createElement('script');
                        // Built with URLSearchParams so the source carries no
                        // raw ampersand. This lives in an HTML attribute, where
                        // an ampersand followed by 'region' is decoded as the
                        // registered-trademark entity and silently corrupts the
                        // URL.
                        const params = new URLSearchParams({
                            key: key,
                            language: @js($locale),
                            region: 'SA',
                        });
                        script.src = 'https://maps.googleapis.com/maps/api/js?' + params.toString();
                        script.async = true;
                        script.onload = resolve;
                        script.onerror = reject;
                        document.head.appendChild(script);
                    });

                    try { window.veltoMapsLoader = loader } catch (e) {}
                }

                loader.then(() => this.draw()).catch(() => {});
            },

            draw() {
                if (! window.google?.maps) return;

                const position = { lat: @js($lat), lng: @js($lng) };
                const map = new google.maps.Map(this.$refs.canvas, {
                    center: position,
                    zoom: 16,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                });
                new google.maps.Marker({ position, map });

                this.live = true;
            },

            copy() {
                navigator.clipboard.writeText(@js($coords)).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 1500);
                });
            },
        }"
        x-init="boot()"
        class="vm-map"
    >
        <div class="vm-map__frame">
            <div x-ref="canvas" class="vm-map__fill"></div>

            @if ($staticMap)
                <a
                    href="{{ $googleUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="vm-map__cover"
                    x-show="! live && ! pictureFailed"
                >
                    <img
                        src="{{ $staticMap }}"
                        alt="{{ __('Booking location') }}"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        class="vm-map__fill"
                        style="object-fit: cover"
                        x-on:error="pictureFailed = true"
                    />
                </a>
            @endif

            {{-- Last resort: neither the live map nor the picture came up. Say
                 so and keep the location reachable, rather than leaving an
                 empty frame or a broken-image icon. --}}
            <div
                class="vm-map__cover vm-map__empty"
                x-show="! live @if ($staticMap) && pictureFailed @endif"
                x-cloak
            >
                <span>{{ __('Map could not be loaded') }}</span>
                <a href="{{ $googleUrl }}" target="_blank" rel="noopener noreferrer" class="vm-map__link">{{ $coords }}</a>
            </div>
        </div>

        {{-- The map shows where. These hand it to a phone, which is what
             dispatch actually needs once they have looked. --}}
        <div class="vm-map__actions">
            <x-filament::button tag="a" :href="$googleUrl" target="_blank" rel="noopener noreferrer" icon="heroicon-m-map" size="sm">
                {{ __('Open in Google Maps') }}
            </x-filament::button>

            <x-filament::button tag="a" :href="$directionsUrl" target="_blank" rel="noopener noreferrer" icon="heroicon-m-arrow-top-right-on-square" color="gray" outlined size="sm">
                {{ __('Directions') }}
            </x-filament::button>

            <x-filament::button x-on:click="copy()" icon="heroicon-m-clipboard-document" color="gray" outlined size="sm">
                <span x-show="! copied">{{ $coords }}</span>
                <span x-show="copied" x-cloak>{{ __('Copied') }}</span>
            </x-filament::button>
        </div>
    </div>
@else
    {{-- Bookings taken before the app captured a pin, and admin-created ones,
         have no coordinates. Saying so beats a map of central Riyadh that
         looks like an answer. --}}
    <div class="vm-map__none">
        {{ __('No location recorded for this booking') }}
    </div>
@endif

{{-- The panel ships Filament's prebuilt stylesheet and no custom theme, so
     utility classes written here never get compiled — the frame used to
     collapse to zero height. Scoped plain CSS instead. --}}
@once
    <style>
        .vm-map { display: flex; flex-direction: column; gap: .75rem; }
        .vm-map__frame { position: relative; height: 20rem; overflow: hidden; border-radius: .75rem; border: 1px solid rgb(0 0 0 / .1); }
        .vm-map__fill { width: 100%; height: 100%; display: block; }
        .vm-map__cover { position: absolute; inset: 0; }
        .vm-map__empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .5rem; padding: 1rem; text-align: center; font-size: .875rem; color: rgb(107 114 128); background: rgb(249 250 251); }
        .vm-map__link { font-weight: 500; text-decoration: underline; color: var(--primary-600); }
        .vm-map__actions { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
        .vm-map__none { border-radius: .75rem; border: 1px dashed rgb(209 213 219); padding: 1.25rem; text-align: center; font-size: .875rem; color: rgb(107 114 128); }
        .dark .vm-map__frame { border-color: rgb(255 255 255 / .1); }
        .dark .vm-map__empty { background: rgb(255 255 255 / .05); color: rgb(156 163 175); }
        .dark .vm-map__none { border-color: rgb(255 255 255 / .15); color: rgb(156 163 175); }
    </style>
@endonce
