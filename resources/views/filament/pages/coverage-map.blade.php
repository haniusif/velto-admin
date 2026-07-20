<x-filament-panels::page>
    <style>
        .cm-scope{--cm-card:#fff;--cm-border:rgba(17,24,39,.08);--cm-fg:#111827;--cm-muted:#6b7280;--cm-hover:#f9fafb;--cm-input:#fff;--cm-track:#cbd5e1;}
        .dark .cm-scope{--cm-card:rgba(255,255,255,.05);--cm-border:rgba(255,255,255,.1);--cm-fg:#fff;--cm-muted:#9ca3af;--cm-hover:rgba(255,255,255,.05);--cm-input:rgba(255,255,255,.05);--cm-track:rgba(255,255,255,.2);}
        .cm-scope{display:flex;flex-direction:column;gap:16px;}
        .cm-tiles{display:flex;flex-wrap:wrap;gap:12px;}
        .cm-tile{flex:1 1 160px;background:var(--cm-card);border:1px solid var(--cm-border);border-radius:12px;padding:16px;}
        .cm-tile-num{font-size:26px;font-weight:800;line-height:1.1;color:var(--cm-fg);}
        .cm-tile-num.green{color:#16a34a;}
        .cm-tile-label{margin-top:4px;font-size:13px;color:var(--cm-muted);}
        .cm-bar{margin-top:10px;height:6px;width:100%;overflow:hidden;border-radius:999px;background:var(--cm-track);}
        .cm-bar-fill{height:100%;border-radius:999px;background:#22c55e;transition:width .3s;}
        .cm-actions{display:flex;flex-direction:column;justify-content:center;gap:8px;flex:1 1 160px;background:var(--cm-card);border:1px solid var(--cm-border);border-radius:12px;padding:16px;}
        .cm-btn{border:none;border-radius:10px;padding:8px 12px;font-size:13px;font-weight:600;cursor:pointer;}
        .cm-btn-green{background:#16a34a;color:#fff;}.cm-btn-green:hover{background:#15803d;}
        .cm-btn-muted{background:var(--cm-hover);color:var(--cm-fg);border:1px solid var(--cm-border);}
        .cm-main{display:flex;flex-wrap:wrap;gap:16px;}
        .cm-map-card{position:relative;flex:1 1 420px;min-width:300px;overflow:hidden;border-radius:12px;border:1px solid var(--cm-border);}
        .cm-map{height:72vh;width:100%;background:#e5e7eb;}
        .cm-legend{position:absolute;bottom:12px;inset-inline-start:12px;display:flex;gap:16px;align-items:center;background:var(--cm-card);border:1px solid var(--cm-border);border-radius:10px;padding:6px 12px;font-size:12px;color:var(--cm-fg);box-shadow:0 2px 8px rgba(0,0,0,.12);}
        .cm-legend span{display:inline-flex;align-items:center;gap:6px;}
        .cm-dot{width:12px;height:12px;border-radius:50%;display:inline-block;}
        .cm-loading{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;gap:8px;background:rgba(229,231,235,.75);color:var(--cm-muted);font-size:14px;}
        .cm-panel{display:flex;flex-direction:column;flex:0 1 340px;min-width:270px;max-height:72vh;overflow:hidden;background:var(--cm-card);border:1px solid var(--cm-border);border-radius:12px;}
        .cm-panel-head{padding:12px;border-bottom:1px solid var(--cm-border);}
        .cm-search-wrap{position:relative;}
        .cm-search{width:100%;box-sizing:border-box;border:1px solid var(--cm-border);background:var(--cm-input);color:var(--cm-fg);border-radius:10px;padding:9px 36px 9px 12px;font-size:14px;}
        .cm-search:focus{outline:none;border-color:#8863E5;box-shadow:0 0 0 2px rgba(136,99,229,.25);}
        .cm-search-icon{position:absolute;inset-inline-end:10px;top:9px;color:var(--cm-muted);}
        .cm-hint{margin-top:8px;font-size:12px;color:var(--cm-muted);}
        .cm-list{flex:1;overflow-y:auto;}
        .cm-row{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:9px 12px;border-bottom:1px solid var(--cm-border);}
        .cm-row:hover{background:var(--cm-hover);}
        .cm-row-name{flex:1;text-align:start;font-size:14px;color:var(--cm-fg);background:none;border:none;cursor:pointer;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;padding:0;}
        .cm-switch{position:relative;width:36px;height:20px;flex-shrink:0;border:none;border-radius:999px;background:var(--cm-track);cursor:pointer;padding:0;transition:background .15s;}
        .cm-switch.on{background:#22c55e;}
        .cm-knob{position:absolute;top:2px;inset-inline-end:2px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.25);transition:transform .15s;}
        .cm-switch.on .cm-knob{transform:translateX(-16px);}
        .dark .cm-switch.on .cm-knob{transform:translateX(-16px);}
        .cm-empty{padding:24px;text-align:center;color:var(--cm-muted);font-size:14px;}
        .cm-error{margin-top:12px;border-radius:10px;background:rgba(239,68,68,.1);color:#b91c1c;padding:12px;font-size:14px;}
        @keyframes cm-spin{to{transform:rotate(360deg)}}
        .cm-spin{width:18px;height:18px;border:2px solid rgba(136,99,229,.3);border-top-color:#8863E5;border-radius:50%;animation:cm-spin .8s linear infinite;}
    </style>

    <div class="cm-scope" wire:ignore x-data="coverageMapPage" x-init="init()" dir="rtl">

        {{-- Stat tiles --}}
        <div class="cm-tiles">
            <div class="cm-tile">
                <div class="cm-tile-num" x-text="total"></div>
                <div class="cm-tile-label">إجمالي الأحياء</div>
            </div>
            <div class="cm-tile">
                <div class="cm-tile-num green" x-text="coveredCount"></div>
                <div class="cm-tile-label">مغطّى</div>
            </div>
            <div class="cm-tile">
                <div class="cm-tile-num"><span x-text="percent"></span>%</div>
                <div class="cm-tile-label">نسبة التغطية</div>
                <div class="cm-bar"><div class="cm-bar-fill" :style="`width:${percent}%`"></div></div>
            </div>
            <div class="cm-actions">
                <button type="button" class="cm-btn cm-btn-green" @click="coverAll(true)">تغطية الكل</button>
                <button type="button" class="cm-btn cm-btn-muted" @click="coverAll(false)">إلغاء الكل</button>
            </div>
        </div>

        {{-- Map + side panel --}}
        <div class="cm-main">
            <div class="cm-map-card">
                <div class="cm-map" x-ref="map"></div>
                <div class="cm-legend">
                    <span><span class="cm-dot" style="background:#16a34a"></span> مغطّى</span>
                    <span><span class="cm-dot" style="background:#9ca3af"></span> غير مغطّى</span>
                </div>
                <div class="cm-loading" x-show="loading">
                    <span class="cm-spin"></span> جارٍ تحميل الخريطة…
                </div>
            </div>

            <div class="cm-panel">
                <div class="cm-panel-head">
                    <div class="cm-search-wrap">
                        <input class="cm-search" type="search" x-model="search" placeholder="ابحث عن حي…">
                        <svg class="cm-search-icon" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.08 3.08a1 1 0 01-1.42 1.42l-3.08-3.08A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="cm-hint"><span x-text="filtered.length"></span> حي · اضغط على الاسم للانتقال إليه</div>
                </div>
                <div class="cm-list">
                    <template x-for="d in filtered" :key="d.id">
                        <div class="cm-row">
                            <button type="button" class="cm-row-name" @click="focus(d.id)" x-text="d.name"></button>
                            <button type="button" class="cm-switch" :class="{ on: d.covered }"
                                    role="switch" :aria-checked="d.covered" @click="toggle(d.id)">
                                <span class="cm-knob"></span>
                            </button>
                        </div>
                    </template>
                    <div class="cm-empty" x-show="filtered.length === 0">لا توجد نتائج</div>
                </div>
            </div>
        </div>

        <p class="cm-error" x-show="error" x-cloak x-text="error"></p>
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
                                this.error = 'تعذّر تحميل خرائط Google — تحقّق من مفتاح الـ API وصلاحياته لهذا النطاق.';
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
