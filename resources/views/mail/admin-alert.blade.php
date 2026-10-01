@php($rtl = preg_match('/\p{Arabic}/u', $title))
<!doctype html>
<html lang="{{ $rtl ? 'ar' : 'en' }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:24px 12px;background:#f4f2fb;font-family:Tahoma,Arial,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e9e5f7;">
        <tr>
            <td style="background:#8863E5;padding:14px 22px;color:#ffffff;font-weight:bold;font-size:16px;">Velto Admin</td>
        </tr>
        <tr>
            <td style="padding:22px;">
                <h1 style="margin:0 0 8px;font-size:18px;line-height:1.4;">{{ $title }}</h1>
                <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#4b5563;">{{ $body }}</p>
                <a href="{{ $url }}" style="display:inline-block;background:#8863E5;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:9px;font-size:14px;font-weight:bold;">
                    {{ $rtl ? 'فتح في لوحة التحكم' : 'Open in admin' }}
                </a>
            </td>
        </tr>
        <tr>
            <td style="padding:12px 22px;border-top:1px solid #f0edf9;font-size:12px;color:#9ca3af;">
                {{ $rtl ? 'تصلك هذه الرسالة لأن بريدك مضاف في صفحة تنبيهات الإدارة.' : 'You get this because your address is listed on the Admin alerts page.' }}
            </td>
        </tr>
    </table>
</body>
</html>
