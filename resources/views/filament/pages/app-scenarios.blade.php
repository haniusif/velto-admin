<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">رحلة حجز العميل</x-slot>
            <x-slot name="description">من تسجيل الدخول حتى تأكيد الحجز والدفع (تطبيق العميل).</x-slot>

            <div class="scenario-diagram">
                @verbatim
                <pre class="mermaid">
flowchart TD
    A([تسجيل الدخول عبر OTP]) --> B[الصفحة الرئيسية]
    B --> C[اختيار باقة الغسيل]
    C --> D[اختيار السيارة]
    D --> E[اختيار موعد متاح]
    E --> F[تحديد الموقع]
    F --> G{طريقة الدفع}
    G -->|المحفظة| H[خصم من رصيد المحفظة]
    G -->|بطاقة| I[بوابة ARB / Neoleap]
    I --> J[صفحة الدفع]
    J --> K{نتيجة الدفع}
    K -->|نجاح| L[تأكيد الحجز]
    K -->|فشل| C
    H --> L
    L --> M[إشعار تأكيد ويظهر الموعد في التطبيق]
                </pre>
                @endverbatim
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">دورة حياة مهمة الموظف</x-slot>
            <x-slot name="description">من إسناد المهمة حتى إنجاز الخدمة (تطبيق الموظف · نقاط /worker/jobs).</x-slot>

            <div class="scenario-diagram">
                @verbatim
                <pre class="mermaid">
flowchart TD
    A([تسجيل دخول الموظف عبر OTP]) --> B[قائمة المهام]
    B --> C[مهمة جديدة مُسنَدة]
    C --> D[قبول المهمة · accept]
    D --> E[بدء التوجّه · start]
    E --> F[الوصول للعميل · arrived]
    F --> G[بدء العمل · start-work]
    G --> H[إنجاز الخدمة · complete]
    H --> I[إشعار العميل وتحديث حالة الموعد]
                </pre>
                @endverbatim
            </div>
        </x-filament::section>
    </div>

    <style>
        .scenario-diagram { display: flex; justify-content: center; overflow-x: auto; padding: 0.5rem 0; }
        .scenario-diagram .mermaid { background: transparent; }
        .scenario-diagram svg { max-width: 100%; height: auto; }
    </style>

    <script src="{{ asset('js/mermaid.min.js') }}"></script>
    <script>
        (function () {
            function renderScenarios() {
                if (! window.mermaid) return;
                var dark = document.documentElement.classList.contains('dark');
                window.mermaid.initialize({
                    startOnLoad: false,
                    securityLevel: 'loose',
                    theme: dark ? 'dark' : 'default',
                    flowchart: { htmlLabels: true, curve: 'basis' },
                });
                try { window.mermaid.run({ querySelector: '.scenario-diagram .mermaid' }); } catch (e) {}
            }

            if (document.readyState !== 'loading') {
                renderScenarios();
            } else {
                document.addEventListener('DOMContentLoaded', renderScenarios);
            }
            document.addEventListener('livewire:navigated', renderScenarios);
        })();
    </script>
</x-filament-panels::page>
