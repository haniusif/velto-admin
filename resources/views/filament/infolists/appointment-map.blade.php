@php
    $record = $getRecord();

    // Cast through float so nothing but a number reaches the URLs below, and
    // so a stored 0,0 counts as missing — that point is the Gulf of Guinea,
    // not Riyadh, and drawing it would look like a real answer.
    $lat = is_numeric($record->latitude) ? (float) $record->latitude : null;
    $lng = is_numeric($record->longitude) ? (float) $record->longitude : null;
    $hasPoint = $lat !== null && $lng !== null && ($lat !== 0.0 || $lng !== 0.0);

    $key = config('services.google_maps.key');

    if ($hasPoint) {
        $point = $lat . ',' . $lng;
        $coords = $lat . ', ' . $lng;
        $googleUrl = 'https://www.google.com/maps/search/?api=1&query=' . $point;
        $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . $point;

        // Static Maps rather than an embedded interactive map: it is the
        // Google API this key is actually authorised for, and an image cannot
        // fail to a grey box with an error only visible in the console.
        // Clicking it opens the real thing.
        $staticMap = filled($key)
            ? 'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query([
                'center' => $point,
                'zoom' => 16,
                'size' => '640x320',
                'scale' => 2,
                'maptype' => 'roadmap',
                'language' => app()->getLocale(),
                'region' => 'SA',
                'markers' => 'color:0x8863E5|' . $point,
                'key' => $key,
            ])
            : null;
    }
@endphp

@if ($hasPoint)
    <div
        x-data="{
            copied: false,
            copy() {
                navigator.clipboard.writeText(@js($coords)).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 1500);
                });
            },
        }"
        class="space-y-3"
    >
        @if ($staticMap)
            <a
                href="{{ $googleUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="block overflow-hidden rounded-xl border border-gray-200 dark:border-white/10"
            >
                <img
                    src="{{ $staticMap }}"
                    alt="{{ __('Booking location') }}"
                    loading="lazy"
                    referrerpolicy="strict-origin-when-cross-origin"
                    class="block h-auto w-full"
                />
            </a>
        @else
            {{-- No key configured: say why there is no picture rather than
                 rendering a broken image. --}}
            <div class="rounded-xl border border-dashed border-gray-300 p-4 text-center text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                {{ __('Map unavailable — no Google Maps key configured') }}
            </div>
        @endif

        {{-- The picture shows where. These hand it to a phone, which is what
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
