<x-filament-panels::page>
    <div wire:ignore x-data="coverageMapPage" x-init="init()" dir="rtl">

        {{-- Stat tiles --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-white p-4 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <div class="text-2xl font-bold text-gray-950 dark:text-white" x-text="total"></div>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">إجمالي الأحياء</div>
            </div>
            <div class="rounded-xl bg-white p-4 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <div class="text-2xl font-bold text-green-600 dark:text-green-500" x-text="coveredCount"></div>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">مغطّى</div>
            </div>
            <div class="rounded-xl bg-white p-4 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <div class="text-2xl font-bold text-gray-950 dark:text-white"><span x-text="percent"></span>%</div>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">نسبة التغطية</div>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                    <div class="h-full rounded-full bg-green-500 transition-all" :style="`width:${percent}%`"></div>
                </div>
            </div>
            <div class="flex flex-col justify-center gap-2 rounded-xl bg-white p-4 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <button type="button" @click="coverAll(true)"
                    class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">
                    تغطية الكل
                </button>
                <button type="button" @click="coverAll(false)"
                    class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-200 dark:hover:bg-white/20">
                    إلغاء الكل
                </button>
            </div>
        </div>

        {{-- Map + side panel --}}
        <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_22rem]">

            {{-- Map --}}
            <div class="relative overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10">
                <div x-ref="map" style="height:74vh;width:100%;background:#e5e7eb"></div>

                {{-- Legend overlay --}}
                <div class="pointer-events-none absolute bottom-3 left-3 flex items-center gap-4 rounded-lg bg-white/90 px-3 py-2 text-xs shadow ring-1 ring-gray-950/5 backdrop-blur dark:bg-gray-900/90 dark:ring-white/10">
                    <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-full" style="background:#16a34a"></span> مغطّى</span>
                    <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-full" style="background:#9ca3af"></span> غير مغطّى</span>
                </div>

                {{-- Loading overlay --}}
                <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-gray-100/80 text-sm text-gray-500 dark:bg-gray-900/80">
                    <svg class="mr-2 h-5 w-5 animate-spin text-primary-500" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"></path>
                    </svg>
                    جارٍ تحميل الخريطة…
                </div>
            </div>

            {{-- Side panel: searchable district list --}}
            <div class="flex max-h-[74vh] flex-col overflow-hidden rounded-xl bg-white ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <div class="border-b border-gray-100 p-3 dark:border-white/10">
                    <div class="relative">
                        <input x-model="search" type="search" placeholder="ابحث عن حي…"
                            class="w-full rounded-lg border-gray-300 bg-white py-2 pr-9 pl-3 text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <svg class="pointer-events-none absolute right-2.5 top-2.5 h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.08 3.08a1 1 0 01-1.42 1.42l-3.08-3.08A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        <span x-text="filtered.length"></span> حي · اضغط على الاسم للانتقال إليه على الخريطة
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <template x-for="d in filtered" :key="d.id">
                        <div class="flex items-center justify-between gap-2 border-b border-gray-50 px-3 py-2 last:border-0 hover:bg-gray-50 dark:border-white/5 dark:hover:bg-white/5">
                            <button type="button" @click="focus(d.id)"
                                class="flex-1 truncate text-right text-sm text-gray-800 dark:text-gray-200"
                                x-text="d.name"></button>
                            <button type="button" @click="toggle(d.id)" role="switch" :aria-checked="d.covered"
                                :class="d.covered ? 'bg-green-500' : 'bg-gray-300 dark:bg-white/20'"
                                class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors">
                                <span :class="d.covered ? 'translate-x-[-18px]' : 'translate-x-[-2px]'"
                                    class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"></span>
                            </button>
                        </div>
                    </template>
                    <div x-show="filtered.length === 0" class="p-6 text-center text-sm text-gray-400">
                        لا توجد نتائج
                    </div>
                </div>
            </div>
        </div>

        <p x-show="error" x-cloak class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-500/10" x-text="error"></p>
    </div>

    @push('scripts')
        <script>
            window.__coverageData = @js($this->districtsForMap());
            window.__coverageKey  = @js($this->googleMapsKey());

            function loadGoogleMaps(key) {
                if (window.__gmapsPromise) return window.__gmapsPromise;
                window.__gmapsPromise = new Promise((resolve, reject) => {
                    if (window.google && window.google.maps) return resolve();
                    const s = document.createElement('script');
                    s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(key);
                    s.async = true; s.defer = true;
                    s.onload = () => resolve();
                    s.onerror = () => reject(new Error('load failed'));
                    document.head.appendChild(s);
                });
                return window.__gmapsPromise;
            }

            function geometryToGroups(geom) {
                if (!geom) return [];
                const ring = (r) => r.map((p) => ({ lat: p[1], lng: p[0] }));
                if (geom.type === 'Polygon') return [geom.coordinates.map(ring)];
                if (geom.type === 'MultiPolygon') return geom.coordinates.map((poly) => poly.map(ring));
                return [];
            }

            document.addEventListener('alpine:init', () => {
                Alpine.data('coverageMapPage', () => {
                    let map, info;
                    const polys = {}; // id -> [google.maps.Polygon] (kept out of Alpine reactivity)

                    return {
                        loading: true,
                        error: null,
                        search: '',
                        districts: (window.__coverageData || []).map((d) => ({
                            id: d.id, name: d.name, covered: !!d.is_covered,
                        })),

                        get total() { return this.districts.length; },
                        get coveredCount() { return this.districts.filter((d) => d.covered).length; },
                        get percent() { return this.total ? Math.round((this.coveredCount / this.total) * 100) : 0; },
                        get filtered() {
                            const q = this.search.trim();
                            return q ? this.districts.filter((d) => d.name.includes(q)) : this.districts;
                        },

                        async init() {
                            try {
                                await loadGoogleMaps(window.__coverageKey);
                                this.draw();
                            } catch (e) {
                                this.error = 'تعذّر تحميل خرائط Google — تحقّق من مفتاح الـ API وصلاحياته لنطاق velto.test.';
                            }
                            this.loading = false;
                        },

                        styleOf(covered, hover = false) {
                            return {
                                fillColor: covered ? '#16a34a' : '#9ca3af',
                                fillOpacity: covered ? 0.45 : 0.12,
                                strokeColor: hover ? '#8863E5' : (covered ? '#15803d' : '#6b7280'),
                                strokeWeight: hover ? 2.5 : (covered ? 1.4 : 0.8),
                            };
                        },

                        applyStyle(id, hover = false) {
                            const rec = this.districts.find((d) => d.id === id);
                            (polys[id] || []).forEach((p) => p.setOptions(this.styleOf(rec.covered, hover)));
                        },

                        draw() {
                            map = new google.maps.Map(this.$refs.map, {
                                center: { lat: 24.7136, lng: 46.6753 },
                                zoom: 11,
                                mapTypeControl: false,
                                streetViewControl: false,
                                fullscreenControl: true,
                                clickableIcons: false,
                            });
                            info = new google.maps.InfoWindow();

                            (window.__coverageData || []).forEach((d) => {
                                const groups = geometryToGroups(d.geometry);
                                polys[d.id] = groups.map((paths) => new google.maps.Polygon({ paths, map, clickable: true }));
                                this.applyStyle(d.id);
                                polys[d.id].forEach((p) => {
                                    p.addListener('click', (ev) => this.toggle(d.id, ev && ev.latLng));
                                    p.addListener('mouseover', () => this.applyStyle(d.id, true));
                                    p.addListener('mouseout', () => this.applyStyle(d.id, false));
                                });
                            });
                        },

                        async toggle(id, latLng = null) {
                            const rec = this.districts.find((d) => d.id === id);
                            rec.covered = !rec.covered;
                            this.applyStyle(id);
                            if (latLng) {
                                info.setContent('<div style="font-family:sans-serif;font-size:13px;color:#111">' +
                                    rec.name + ' — ' + (rec.covered ? 'مغطّى ✅' : 'غير مغطّى') + '</div>');
                                info.setPosition(latLng);
                                info.open(map);
                            }
                            try {
                                await this.$wire.toggleDistrict(id);
                            } catch (e) {
                                rec.covered = !rec.covered;
                                this.applyStyle(id);
                            }
                        },

                        focus(id) {
                            const shapes = polys[id] || [];
                            if (!shapes.length || !map) return;
                            const b = new google.maps.LatLngBounds();
                            shapes.forEach((p) => p.getPath().forEach((pt) => b.extend(pt)));
                            map.fitBounds(b);
                            this.applyStyle(id, true);
                            setTimeout(() => this.applyStyle(id, false), 1400);
                        },

                        async coverAll(value) {
                            this.districts.forEach((d) => { d.covered = value; this.applyStyle(d.id); });
                            try {
                                await this.$wire.setAllCovered(value);
                            } catch (e) {
                                this.error = 'تعذّر حفظ التغيير الجماعي.';
                            }
                        },
                    };
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
