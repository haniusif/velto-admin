<x-filament-panels::page>
    <style>
        .tsc-scope{--tsc-card:#fff;--tsc-border:rgba(17,24,39,.08);--tsc-fg:#111827;--tsc-muted:#6b7280;}
        .dark .tsc-scope{--tsc-card:rgba(255,255,255,.05);--tsc-border:rgba(255,255,255,.1);--tsc-fg:#f9fafb;--tsc-muted:#9ca3af;}
        .tsc-legend{display:flex;flex-wrap:wrap;gap:16px;align-items:center;margin-bottom:12px;font-size:13px;color:var(--tsc-fg);}
        .tsc-legend span{display:inline-flex;align-items:center;gap:6px;}
        .tsc-dot{width:12px;height:12px;border-radius:3px;display:inline-block;}
        .tsc-card{background:var(--tsc-card);border:1px solid var(--tsc-border);border-radius:12px;padding:16px;}
        #tsc-calendar{--fc-border-color:var(--tsc-border);--fc-page-bg-color:transparent;--fc-neutral-bg-color:rgba(136,99,229,.06);color:var(--tsc-fg);}
        #tsc-calendar .fc .fc-button{background:#8863E5;border-color:#8863E5;text-transform:none;box-shadow:none;}
        #tsc-calendar .fc .fc-button:hover{background:#744fd6;border-color:#744fd6;}
        #tsc-calendar .fc .fc-button-primary:disabled{background:#b8a6ee;border-color:#b8a6ee;}
        #tsc-calendar .fc-daygrid-day-number,#tsc-calendar .fc-col-header-cell-cushion{color:var(--tsc-fg);text-decoration:none;}
        #tsc-calendar .fc-daygrid-event{font-size:11px;padding:1px 4px;border-radius:4px;}
        #tsc-calendar a.fc-daygrid-day-number,#tsc-calendar .fc-toolbar-title{color:var(--tsc-fg);}
    </style>

    <div class="tsc-scope" wire:ignore x-data x-init="window.initTimeSlotCalendar && window.initTimeSlotCalendar($refs.cal, @js($this->calendarEvents()))">
        <div class="tsc-legend">
            <span><span class="tsc-dot" style="background:#16a34a"></span> متاح</span>
            <span><span class="tsc-dot" style="background:#ef4444"></span> مكتمل الحجز</span>
            <span><span class="tsc-dot" style="background:#9ca3af"></span> غير مفعّل</span>
            <span style="color:var(--tsc-muted)">اضغط على أي موعد لتعديله.</span>
        </div>
        <div class="tsc-card">
            <div id="tsc-calendar" x-ref="cal"></div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script>
            window.initTimeSlotCalendar = function (el, events) {
                if (!el || el.dataset.ready) return;
                el.dataset.ready = '1';
                const build = () => {
                    const cal = new FullCalendar.Calendar(el, {
                        initialView: 'dayGridMonth',
                        locale: 'ar',
                        direction: 'rtl',
                        firstDay: 6, // Saturday
                        height: 'auto',
                        headerToolbar: {
                            start: 'title',
                            center: '',
                            end: 'dayGridMonth,timeGridWeek today prev,next',
                        },
                        buttonText: { today: 'اليوم', month: 'شهر', week: 'أسبوع' },
                        events: events,
                        eventDisplay: 'block',
                        displayEventTime: false,
                        eventClick: function (info) {
                            if (info.event.url) {
                                info.jsEvent.preventDefault();
                                window.location.assign(info.event.url);
                            }
                        },
                    });
                    cal.render();
                };
                if (window.FullCalendar) { build(); return; }
                // Script may still be loading — poll briefly.
                let tries = 0;
                const t = setInterval(() => {
                    if (window.FullCalendar) { clearInterval(t); build(); }
                    else if (++tries > 100) { clearInterval(t); }
                }, 50);
            };
        </script>
    @endpush
</x-filament-panels::page>
