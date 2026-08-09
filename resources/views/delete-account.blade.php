<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>حذف حساب فيلتو — Delete your Velto account</title>
    <style>
        :root{--purple:#8863E5;--purple700:#5C42B8;--ink:#111827;--muted:#6b7280;--line:rgba(17,24,39,.10);--bg:#faf9fd;--card:#fff}
        @media (prefers-color-scheme:dark){:root{--ink:#f9fafb;--muted:#9ca3af;--line:rgba(255,255,255,.12);--bg:#14121c;--card:#1c1926}}
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--ink);
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Tahoma,Arial,sans-serif;line-height:1.7}
        .hero{background:linear-gradient(135deg,var(--purple700),var(--purple));color:#fff;padding:44px 20px 52px;text-align:center}
        .hero h1{margin:0 0 8px;font-size:26px;font-weight:800}
        .hero p{margin:0;opacity:.9;font-size:15px}
        .wrap{max-width:760px;margin:-28px auto 56px;padding:0 16px}
        .card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px 22px;margin-bottom:16px}
        h2{font-size:18px;margin:0 0 12px}
        ol,ul{margin:0;padding-inline-start:20px}
        li{margin-bottom:7px}
        .muted{color:var(--muted);font-size:14px}
        .btn{display:inline-block;background:var(--purple);color:#fff;text-decoration:none;
            padding:12px 20px;border-radius:12px;font-weight:600;margin:6px 6px 0 0}
        .btn.alt{background:transparent;color:var(--purple);border:1px solid var(--purple)}
        .en{direction:ltr;text-align:left}
        .sep{height:1px;background:var(--line);margin:26px 0}
        code{background:rgba(136,99,229,.12);padding:2px 6px;border-radius:6px;font-size:13px}
    </style>
</head>
<body>
    <div class="hero">
        <h1>حذف حساب فيلتو</h1>
        <p>Delete your Velto account and data</p>
    </div>

    <div class="wrap">
        <div class="card">
            <h2>الحذف من داخل التطبيق</h2>
            <ol>
                <li>افتح تطبيق فيلتو وسجّل الدخول</li>
                <li>اذهب إلى <strong>حسابي</strong></li>
                <li>اختر <strong>حذف الحساب</strong></li>
                <li>أكّد الطلب — يتم الحذف فوراً ولا يمكن التراجع عنه</li>
            </ol>
        </div>

        <div class="card">
            <h2>طلب الحذف بدون التطبيق</h2>
            <p class="muted">إذا لم يعد بإمكانك الدخول إلى التطبيق، أرسل لنا طلباً من رقم جوالك المسجّل وسنحذف الحساب.</p>
            <a class="btn" href="mailto:{{ $supportEmail }}?subject={{ rawurlencode('طلب حذف الحساب') }}&body={{ rawurlencode("أرغب في حذف حسابي في فيلتو.\nرقم الجوال المسجّل: ") }}">
                راسلنا على البريد
            </a>
            @if($whatsapp)
                <a class="btn alt" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('أرغب في حذف حسابي في فيلتو') }}">واتساب</a>
            @endif
            <p class="muted" style="margin-top:14px">
                البريد: <code>{{ $supportEmail }}</code>
                @if($phone) — الهاتف: <code>{{ $phone }}</code> @endif
            </p>
            <p class="muted">نعالج الطلبات خلال 30 يوماً كحد أقصى، وعادةً خلال أيام قليلة.</p>
        </div>

        <div class="card">
            <h2>ما الذي يُحذف</h2>
            <ul>
                <li>حسابك وبياناتك الشخصية (الاسم، الجوال، البريد)</li>
                <li>سياراتك المحفوظة</li>
                <li>عناوينك المحفوظة</li>
                <li>حجوزاتك وسجلّها</li>
                <li>باقاتك وزياراتها المتبقية</li>
                <li>رصيد محفظتك وحركاتها</li>
                <li>أجهزتك المسجّلة للإشعارات وإشعاراتك</li>
                <li>تقييماتك وأكواد الخصم المستخدمة</li>
            </ul>
            <p class="muted" style="margin-top:12px">
                الحذف نهائي. رصيد المحفظة المتبقي يسقط بالحذف ولا يمكن استرجاعه.
            </p>
        </div>

        <div class="card">
            <h2>ما الذي يبقى ولماذا</h2>
            <p class="muted">
                تبقى سجلات المعاملات المالية (المبلغ، العملة، مرجع بوابة الدفع، الحالة) بعد فصلها
                عن حسابك، فلا تحتوي على ما يعرّف بك. نحتفظ بها للالتزامات المحاسبية والنظامية فقط.
            </p>
        </div>

        <div class="sep"></div>

        <div class="card en">
            <h2>Delete your Velto account</h2>
            <p class="muted">In the app: <strong>Profile → Delete account → confirm</strong>. Deletion is immediate and permanent.</p>
            <p class="muted">
                No longer able to sign in? Email <code>{{ $supportEmail }}</code> from your registered
                phone number and we will delete the account. Requests are handled within 30 days.
            </p>
            <p class="muted">
                <strong>Deleted:</strong> account and personal details, saved vehicles, saved addresses,
                bookings, packages, wallet balance and transactions, notification devices, reviews and
                promo redemptions.<br>
                <strong>Retained:</strong> payment transaction records, detached from your account so they
                no longer identify you, kept for accounting and legal obligations.
            </p>
        </div>

        <p class="muted" style="text-align:center">
            <a href="{{ $website }}" style="color:var(--purple)">{{ $websiteDisplay }}</a>
        </p>
    </div>
</body>
</html>
