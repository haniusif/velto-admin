<x-filament-widgets::widget>
    <div dir="rtl" style="border:1px solid #fcd34d;background:#fffbeb;border-radius:12px;padding:16px;">
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"
                 style="width:24px;height:24px;flex-shrink:0;margin-top:2px;">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>
            </svg>
            <div style="flex:1;min-width:0;">
                <h3 style="margin:0;font-size:14px;font-weight:700;color:#92400e;">
                    مهمة cron غير مُفعّلة — المهام المجدوَلة لا تعمل
                </h3>
                <p style="margin:4px 0 0;font-size:14px;color:#b45309;">
                    الحجوزات غير المدفوعة لن تُلغى تلقائياً، وأي مهام مجدوَلة أخرى لن تُنفَّذ.
                    أضِف هذا السطر إلى crontab على الخادم (مرة واحدة):
                </p>
                <pre style="margin:8px 0 0;overflow-x:auto;border-radius:8px;background:#fef3c7;padding:8px 12px;font-size:12px;color:#78350f;"><code>{{ $cron }}</code></pre>
                <p style="margin:8px 0 0;font-size:12px;color:#b45309;opacity:.85;">
                    @if ($lastRun)
                        آخر تشغيل للمجدوِل: {{ $lastRun->diffForHumans() }}. سيختفي هذا التنبيه تلقائياً عند عمل المجدوِل بانتظام.
                    @else
                        لم يُسجَّل أي تشغيل للمجدوِل بعد. سيختفي هذا التنبيه تلقائياً بمجرد أن يعمل.
                    @endif
                </p>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
