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

                // Google calls this globally when the key is rejected, which
                // can happen after the map object was already built.
                window.gm_authFailure = () => { this.live = false };

                if (window.google?.maps) { this.draw(); return }

                window.veltoMapsLoader ??= new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    // Built with URLSearchParams so the source carries no raw
                    // ampersand. This lives in an HTML attribute, where an
                    // ampersand followed by 'region' is decoded as the
                    // registered-trademark entity and silently corrupts the URL.
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

                window.veltoMapsLoader.then(() => this.draw()).catch(() => {});
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
        class="space-y-3"
    >
        <div class="relative h-80 overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
            <div x-ref="canvas" class="h-full w-full"></div>

            @if ($staticMap)
                <a
                    href="{{ $googleUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="absolute inset-0"
                    x-show="! live && ! pictureFailed"
                >
                    <img
                        src="{{ $staticMap }}"
                        alt="{{ __('Booking location') }}"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        class="h-full w-full object-cover"
                        x-on:error="pictureFailed = true"
                    />
                </a>
            @endif

            {{-- Last resort: neither the live map nor the picture came up. Say
                 so and keep the location reachable, rather than leaving an
                 empty frame or a broken-image icon. --}}
            <div
                class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gray-50 p-4 text-center text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400"
                x-show="! live @if ($staticMap) && pictureFailed @endif"
                x-cloak
            >
                <span>{{ __('Map could not be loaded') }}</span>
                <a
                    href="{{ $googleUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-medium text-primary-600 underline dark:text-primary-400"
                >{{ $coords }}</a>
            </div>
        </div>

        {{-- The map shows where. These hand it to a phone, which is what
             dispatch actually needs once they have looked. --}}
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ $googleUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white hover:bg-primary-500"
            >
                {{ __('Open in Google Maps') }}
            </a>

            <a
                href="{{ $directionsUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-gray-200 dark:hover:bg-white/5"
            >
                {{ __('Directions') }}
            </a>

            <button
                type="button"
                x-on:click="copy()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/20 dark:text-gray-200 dark:hover:bg-white/5"
            >
                <span x-show="! copied">{{ $coords }}</span>
                <span x-show="copied" x-cloak class="text-success-600 dark:text-success-400">
                    {{ __('Copied') }}
                </span>
            </button>
        </div>
    </div>
@else
    {{-- Bookings taken before the app captured a pin, and admin-created ones,
         have no coordinates. Saying so beats a map of central Riyadh that
         looks like an answer. --}}
    <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
        {{ __('No location recorded for this booking') }}
        @if (filled($record->address_label))
            <div class="mt-1 font-medium text-gray-700 dark:text-gray-200">
                {{ $record->address_label }}
            </div>
        @endif
    </div>
@endif
