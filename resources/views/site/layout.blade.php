@php
  $support = $support ?? app(\App\Http\Controllers\Site\SiteController::class)->supportContacts();
  $whatsapp = $support['support.whatsapp'] ?? '966559809687';
  $phone = $support['support.phone'] ?? '+966559809687';
  $solidNav = $solidNav ?? true;
@endphp
<!doctype html>
<html lang="ar" dir="rtl" data-lang="ar">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ isset($title) ? $title.' · ' : '' }}Velto · فيلتو — الخيار الذكي لغسيل سيارتك</title>
<meta name="description" content="{{ $description ?? 'فيلتو — خدمة عناية بالسيارات متنقلة. نصلك أينما كنت في الرياض. تجربة شخصية وجودة تفوق توقعاتك.' }}">
<meta name="theme-color" content="#8863E5">
{{-- Also as a meta tag: the hosting layer replaces the CSP *header* with its own. --}}
<meta http-equiv="Content-Security-Policy" content="{{ \App\Http\Middleware\SecurityHeaders::SITE_CSP }}">
<link rel="icon" href="/img/logo-velto.png">
<link rel="stylesheet" href="/site/site.css?v={{ filemtime(public_path('site/site.css')) }}">
<script>
  // Apply the stored language before first paint to avoid a flash.
  (function () { try { var l = localStorage.getItem('velto.lang'); if (l === 'en' || l === 'ar') { var h = document.documentElement; h.setAttribute('lang', l); h.setAttribute('dir', l === 'ar' ? 'rtl' : 'ltr'); h.setAttribute('data-lang', l); } } catch (e) {} })();
</script>
<script src="/site/app.js?v={{ filemtime(public_path('site/app.js')) }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
@stack('head')
</head>
<body>

<header class="nav {{ $solidNav ? 'nav--solid' : '' }}" id="nav" x-data>
  <div class="container nav__inner">
    <a href="/" class="nav__brand" aria-label="Velto">
      <img src="/img/logo-velto.png" alt="Velto" class="logo-img">
    </a>

    <nav class="nav__links" aria-label="Primary">
      <a href="/services" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></a>
      <a href="/plans" data-i18n><span data-ar>الباقات</span><span data-en>Plans</span></a>
      <a href="/coverage" data-i18n><span data-ar>التغطية</span><span data-en>Coverage</span></a>
      <a href="/faq" data-i18n><span data-ar>الأسئلة</span><span data-en>FAQ</span></a>
      <a href="/#contact" data-i18n><span data-ar>تواصل</span><span data-en>Contact</span></a>
    </nav>

    <div class="nav__right">
      <div class="lang-toggle" role="group" aria-label="Language">
        <button type="button" data-set-lang="ar" class="is-active">AR</button>
        <button type="button" data-set-lang="en">EN</button>
      </div>
      <template x-if="$store.auth.user">
        <a href="/account" class="nav__user nav__cta">
          <span class="avatar" x-text="$store.auth.initial"></span>
          <span x-text="$store.auth.user.name || Velto.t('nav.account')"></span>
        </a>
      </template>
      <template x-if="!$store.auth.user">
        <a href="/login" class="btn btn--ghost btn--sm nav__cta" style="color:inherit" data-i18n><span data-ar>تسجيل الدخول</span><span data-en>Sign in</span></a>
      </template>
      <a href="/book" class="btn btn--white nav__cta" data-i18n>
        <span data-ar>احجز الآن</span><span data-en>Book now</span>
        {!! $arrow !!}
      </a>
      <button class="nav__menu" id="menuBtn" aria-label="Menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>
</header>

<div class="drawer" id="drawer" x-data>
  <div class="drawer__panel">
    <div class="drawer__head">
      <img src="/img/logo-velto.png" alt="Velto" class="logo-img">
      <button class="drawer__close" id="drawerClose" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <a class="drawer__link" href="/services" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></a>
    <a class="drawer__link" href="/plans" data-i18n><span data-ar>الباقات</span><span data-en>Plans</span></a>
    <a class="drawer__link" href="/coverage" data-i18n><span data-ar>مناطق التغطية</span><span data-en>Coverage</span></a>
    <a class="drawer__link" href="/faq" data-i18n><span data-ar>الأسئلة الشائعة</span><span data-en>FAQ</span></a>
    <a class="drawer__link" href="/#contact" data-i18n><span data-ar>تواصل معنا</span><span data-en>Contact</span></a>
    <template x-if="$store.auth.user">
      <a class="drawer__link" href="/account" data-i18n><span data-ar>حسابي</span><span data-en>My account</span></a>
    </template>
    <template x-if="!$store.auth.user">
      <a class="drawer__link" href="/login" data-i18n><span data-ar>تسجيل الدخول</span><span data-en>Sign in</span></a>
    </template>
    <a href="/book" class="btn btn--primary" data-i18n><span data-ar>احجز الآن</span><span data-en>Book now</span>{!! $arrow !!}</a>
  </div>
</div>

@yield('content')

<footer>
  <div class="container">
    <div class="footer__top">
      <div>
        <a href="/" class="footer__brand"><img src="/img/logo-velto.png" alt="Velto"></a>
        <p class="footer__tagline" data-i18n="block">
          <span data-ar>عناية بالسيارات للأفراد. Velto تأتي إليك — في بيتك، في عملك، في يومك.</span>
          <span data-en>Mobile car-care for individuals. Velto comes to you — your home, your office, your everyday.</span>
        </p>
      </div>
      <div>
        <h4 class="footer__h" data-i18n><span data-ar>روابط</span><span data-en>Links</span></h4>
        <ul class="footer__list">
          <li><a href="/services" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></a></li>
          <li><a href="/plans" data-i18n><span data-ar>الباقات</span><span data-en>Plans</span></a></li>
          <li><a href="/coverage" data-i18n><span data-ar>مناطق التغطية</span><span data-en>Coverage</span></a></li>
          <li><a href="/faq" data-i18n><span data-ar>الأسئلة الشائعة</span><span data-en>FAQ</span></a></li>
          <li><a href="/terms" data-i18n><span data-ar>الشروط والأحكام</span><span data-en>Terms of Service</span></a></li>
          <li><a href="/privacy" data-i18n><span data-ar>سياسة الخصوصية</span><span data-en>Privacy Policy</span></a></li>
          <li><a href="/delete-account" data-i18n><span data-ar>حذف الحساب</span><span data-en>Delete account</span></a></li>
        </ul>
      </div>
      <div>
        <h4 class="footer__h" data-i18n><span data-ar>تواصل</span><span data-en>Contact</span></h4>
        <ul class="footer__list">
          <li class="tnum"><a href="tel:{{ $phone }}" dir="ltr" style="display:inline-block;">{{ $phone }}</a></li>
          <li><a href="https://wa.me/{{ $whatsapp }}">WhatsApp</a></li>
          <li><a href="mailto:{{ $support['support.email_general'] ?? 'info@velto.sa' }}">{{ $support['support.email_general'] ?? 'info@velto.sa' }}</a></li>
          <li><a href="https://instagram.com/Veltoapp" target="_blank" rel="noopener">Instagram · @Veltoapp</a></li>
          <li><a href="https://tiktok.com/@Veltoapp" target="_blank" rel="noopener">TikTok · @Veltoapp</a></li>
        </ul>
      </div>
      <div>
        <h4 class="footer__h" data-i18n><span data-ar>الشركة</span><span data-en>Company</span></h4>
        <ul class="footer__list">
          <li data-i18n="block">
            <span data-ar>شركة Velto لحلول العناية بالسيارات المتنقلة</span>
            <span data-en>Velto Mobile Car Care Solutions Co.</span>
          </li>
          <li class="tnum">CR 7050549497</li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <span data-i18n="block">
        <span data-ar>جميع الحقوق محفوظة © {{ date('Y') }} — فيلتو · Velto</span>
        <span data-en>© {{ date('Y') }} Velto. All rights reserved.</span>
      </span>
      <span data-i18n="block">
        <span data-ar>الرياض · المملكة العربية السعودية</span>
        <span data-en>Riyadh · Kingdom of Saudi Arabia</span>
      </span>
    </div>
  </div>
</footer>

<div x-data x-cloak x-show="$store.ui.toast" x-transition class="toast" x-text="$store.ui.toast"></div>

<script>
  (function () {
    var nav = document.getElementById('nav');
    var onScroll = function () { nav.classList.toggle('is-scrolled', window.scrollY > 24); };
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
    var drawer = document.getElementById('drawer'), open = document.getElementById('menuBtn'), close = document.getElementById('drawerClose');
    var toggle = function (s) { drawer.classList.toggle('is-open', s); document.body.style.overflow = s ? 'hidden' : ''; };
    open.addEventListener('click', function () { toggle(true); });
    close.addEventListener('click', function () { toggle(false); });
    drawer.addEventListener('click', function (e) { if (e.target === drawer) toggle(false); });
    drawer.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { toggle(false); }); });
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) { entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } }); }, { rootMargin: '0px 0px -8% 0px', threshold: 0.04 });
      document.querySelectorAll('[data-reveal]').forEach(function (el) { io.observe(el); });
    } else { document.querySelectorAll('[data-reveal]').forEach(function (el) { el.classList.add('is-in'); }); }
  })();
</script>
@stack('scripts')
</body>
</html>
