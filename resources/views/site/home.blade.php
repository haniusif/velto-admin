@extends('site.layout')

@php
  $spark = '<svg class="spark" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c.6 6 4.4 9.8 12 12-7.6 2.2-11.4 6-12 12-.6-6-4.4-9.8-12-12C7.6 9.8 11.4 6 12 0z" fill="currentColor"/></svg>';
  $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>';
  $whatsapp = $support['support.whatsapp'] ?? '966559809687';
  $phone = $support['support.phone'] ?? '+966559809687';
  $email = $support['support.email_general'] ?? 'info@velto.sa';
  $tiles = ['tile--purple tile--wide tile--tall', 'tile--half', 'tile--ink tile--half', 'tile--mint tile--third', 'tile--lav tile--third'];
  $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
@endphp

@section('content')
<main id="top">

  <!-- HERO -->
  <section class="hero" id="hero">
    <div class="hero__pattern" aria-hidden="true"></div>
    <div class="hero__blob hero__blob--1" aria-hidden="true"></div>
    <div class="hero__blob hero__blob--2" aria-hidden="true"></div>

    <div class="container hero__grid">
      <div class="hero__copy" data-reveal>
        <span class="eyebrow eyebrow--pill eyebrow--onpurple" data-i18n>
          <span data-ar>عناية بالسيارات · الرياض</span>
          <span data-en>Mobile car care · Riyadh</span>
        </span>
        <h1 class="hero__head" data-i18n="block">
          <span data-ar>الخيار الذكي<br>لغسيل <span class="u">سيارتك</span></span>
          <span data-en>Car care that<br>comes <span class="u">to&nbsp;you.</span></span>
        </h1>
        <p class="hero__sub" data-i18n="block">
          <span data-ar>فريق Velto المدرّب يصلك أينما كنت — في بيتك، في عملك، في يومك. احجز من الموقع أو التطبيق، وادفع بالمحفظة أو البطاقة.</span>
          <span data-en>A trained Velto detailer comes to your driveway, your office, your everyday. Book here or in the app, pay by wallet or card.</span>
        </p>
        <div class="hero__ctas">
          <a href="/book" class="btn btn--white" data-i18n>
            <span data-ar>احجز الآن</span><span data-en>Book now</span>
            {!! $arrow !!}
          </a>
          <a href="https://wa.me/{{ $whatsapp }}" class="btn btn--glass" data-i18n>
            <span data-ar>واتساب</span><span data-en>WhatsApp</span>
          </a>
        </div>
        <div class="hero__meta">
          <span class="hero__meta-item" data-i18n>{!! $check !!}<span data-ar>خدمة متنقلة ١٠٠٪</span><span data-en>100% mobile</span></span>
          <span class="hero__meta-item" data-i18n>{!! $check !!}<span data-ar>مواعيد نفس اليوم</span><span data-en>Same-day slots</span></span>
          <span class="hero__meta-item" data-i18n>{!! $check !!}<span data-ar>كل أيام الأسبوع</span><span data-en>Every day</span></span>
        </div>
      </div>

      {{-- Live services in the showcase card, straight from the catalog --}}
      <aside class="showcase" data-reveal data-delay="1">
        <div class="showcase__inner">
          <span class="showcase__tag" data-i18n><span class="dot"></span><span data-ar>احجز في دقيقة</span><span data-en>Book in a minute</span></span>
          <h2 class="showcase__title" data-i18n><span data-ar>اختر خدمتك</span><span data-en>Pick your service</span></h2>
          <p class="showcase__note" data-i18n><span data-ar>السعر النهائي حسب حجم سيارتك</span><span data-en>Final price depends on your car size</span></p>

          @foreach($services->take(3) as $i => $s)
          <a href="/book?service={{ $s->id }}" class="slot {{ $i === 0 ? 'is-active' : '' }} tnum">
            <div class="slot__l">
              <strong><x-i18n :ar="$s->name_ar" :en="$s->name" /></strong>
              <small dir="ltr">{{ $fmt($s->price) }} SAR · {{ $s->duration_minutes }} min</small>
            </div>
            <span class="slot__pill" data-i18n><span data-ar>احجز</span><span data-en>Book</span></span>
          </a>
          @endforeach

          <a href="/book" class="btn btn--primary showcase__cta" data-i18n>
            <span data-ar>ابدأ الحجز</span><span data-en>Start booking</span>
            {!! $arrow !!}
          </a>
        </div>
        <div class="showcase__float">
          <span class="star">{!! $spark !!}</span>
          <span data-i18n>
            <span data-ar>عناية شخصية<small>لكل تفصيل</small></span>
            <span data-en>Personal care<small>every detail</small></span>
          </span>
        </div>
      </aside>
    </div>

    <div class="hero__wave" aria-hidden="true">
      <svg viewBox="0 0 1440 90" preserveAspectRatio="none"><path d="M0 60 C 240 100 480 20 720 40 C 960 60 1200 100 1440 50 L1440 90 L0 90 Z" fill="#FAFAFB"/></svg>
    </div>
  </section>

  <!-- TRUST STRIP -->
  <div class="trust">
    <div class="container">
      <div class="trust__grid">
        <div class="trust__cell" data-reveal>
          <div class="trust__num tnum">100%</div>
          <div class="trust__lbl" data-i18n><span data-ar>خدمة متنقلة</span><span data-en>Mobile service</span></div>
        </div>
        <div class="trust__cell" data-reveal data-delay="1">
          <div class="trust__num tnum">7/7</div>
          <div class="trust__lbl" data-i18n><span data-ar>أيام الأسبوع</span><span data-en>Days a week</span></div>
        </div>
        <div class="trust__cell" data-reveal data-delay="2">
          <div class="trust__num" data-i18n><span data-ar>نفس اليوم</span><span data-en>Same-day</span></div>
          <div class="trust__lbl" data-i18n><span data-ar>مواعيد متاحة</span><span data-en>Booking</span></div>
        </div>
        <div class="trust__cell" data-reveal data-delay="3">
          <div class="trust__num" data-i18n><span data-ar>الرياض</span><span data-en>Riyadh</span></div>
          <div class="trust__lbl" data-i18n><span data-ar>وأينما كنت</span><span data-en>Wherever you are</span></div>
        </div>
      </div>
    </div>
  </div>

  {{-- PROMOTIONS — the same slides the app shows --}}
  @if($sliders->isNotEmpty())
  <section id="promos" style="padding-top: 0;">
    <div class="container">
      <div class="promos" x-data="{ i: 0, n: {{ $sliders->count() }}, timer: null, start() { this.timer = setInterval(() => this.i = (this.i + 1) % this.n, 4500) } }" x-init="start()">
        <div class="promos__track">
          @foreach($sliders as $k => $sl)
          <a href="/book" class="promos__slide" x-show="i === {{ $k }}" x-transition.opacity>
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($sl->image_path) }}" alt="Velto" loading="{{ $k === 0 ? 'eager' : 'lazy' }}">
          </a>
          @endforeach
        </div>
        <div class="promos__dots">
          @foreach($sliders as $k => $sl)
          <button type="button" @click="i = {{ $k }}; clearInterval(timer); start()" :class="{ 'is-active': i === {{ $k }} }" aria-label="Slide {{ $k + 1 }}"></button>
          @endforeach
        </div>
      </div>
    </div>
  </section>
  @endif

  <!-- HOW IT WORKS -->
  <section id="how">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>كيف نعمل</span><span data-en>How it works</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>ثلاث خطوات، <span class="hl">وسيارتك كالجديدة</span></span>
          <span data-en>Three steps to a <span class="hl">brand-new shine</span></span>
        </h2>
      </div>
      <div class="steps">
        <div class="step" data-reveal>
          <span class="step__ghost">01</span><div class="step__n">01</div>
          <h3 class="step__t" data-i18n><span data-ar>احجز في دقيقة</span><span data-en>Book in a minute</span></h3>
          <p class="step__d" data-i18n><span data-ar>اختر الخدمة والسيارة والموقع والوقت من هنا أو من التطبيق. بدون مكالمات ولا انتظار.</span><span data-en>Choose the service, car, location and time — here or in the app. No calls, no waiting.</span></p>
        </div>
        <div class="step" data-reveal data-delay="1">
          <span class="step__ghost">02</span><div class="step__n">02</div>
          <h3 class="step__t" data-i18n><span data-ar>نصل إليك</span><span data-en>We come to you</span></h3>
          <p class="step__d" data-i18n><span data-ar>يصلك متخصص Velto بكامل أدواته ونظام الغسيل اللامائي. موقف عادي يكفي — لا ماء ولا تصريف.</span><span data-en>A Velto detailer arrives fully equipped with a waterless system. A regular parking spot is all it takes.</span></p>
        </div>
        <div class="step" data-reveal data-delay="2">
          <span class="step__ghost">03</span><div class="step__n">03</div>
          <h3 class="step__t" data-i18n><span data-ar>استمتع باللمعان</span><span data-en>Enjoy the shine</span></h3>
          <p class="step__d" data-i18n><span data-ar>سيارة نظيفة ولامعة دون أن تغادر مكانك. قيّم الخدمة بعد كل زيارة.</span><span data-en>A spotless, gleaming car without leaving your spot. Rate the visit when it's done.</span></p>
        </div>
      </div>
    </div>
  </section>

  <!-- SERVICES — from the catalog -->
  <section id="services" style="background: var(--bg);">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>اختر ما تحتاجه <span class="hl">سيارتك</span></span>
          <span data-en>Pick what your <span class="hl">car needs</span></span>
        </h2>
        <p class="section__sub" data-i18n="block">
          <span data-ar>كل زيارة يتولاها فريق Velto المدرّب بأدواته ومواده الخاصة. أنت لا توفّر شيئًا.</span>
          <span data-en>Every visit is run by a trained Velto detailer with their own kit and products. You supply nothing.</span>
        </p>
      </div>

      <div class="bento">
        @foreach($services as $i => $s)
        @php $cls = $tiles[$i % count($tiles)]; $dark = str_contains($cls, 'purple') || str_contains($cls, 'ink'); @endphp
        <article class="tile {{ $cls }}" data-reveal data-delay="{{ $i % 3 }}">
          @if($dark)<div class="tile__pattern" aria-hidden="true"></div>@endif
          <div class="tile__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13M5 13h14v4a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H8v1a1 1 0 01-1 1H6a1 1 0 01-1-1v-4z"/><circle cx="7.5" cy="15.5" r="1"/><circle cx="16.5" cy="15.5" r="1"/></svg>
          </div>
          <span class="tile__k">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
          <h3 class="tile__t"><x-i18n :ar="$s->name_ar" :en="$s->name" /></h3>
          <p class="tile__d"><x-i18n :ar="$s->description_ar" :en="$s->description" block /></p>
          <p class="tile__d tnum" style="margin-top:6px; font-weight:700;">
            <x-i18n ar="ابتداءً من" en="From" /> <span dir="ltr">{{ $fmt($s->price) }} SAR</span> · <span dir="ltr">{{ $s->duration_minutes }}</span> <x-i18n ar="دقيقة" en="min" />
          </p>
          <a href="/book?service={{ $s->id }}" class="tile__link" @if(!$dark) style="color: var(--purple);" @endif data-i18n><span data-ar>احجز الآن</span><span data-en>Book now</span>{!! $arrow !!}</a>
        </article>
        @endforeach

        @if($services->count() < 3)
        <article class="tile tile--mint tile--third" data-reveal data-delay="1">
          <div class="tile__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></div>
          <h3 class="tile__t" data-i18n><span data-ar>مواد آمنة</span><span data-en>Safe products</span></h3>
          <p class="tile__d" data-i18n><span data-ar>مُختبرة وآمنة تمامًا على طلاء سيارتك.</span><span data-en>Tested and fully safe on your paint.</span></p>
        </article>
        <article class="tile tile--lav tile--third" data-reveal data-delay="2">
          <div class="tile__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h13l3-3v6l-3-3M3 6h10M3 18h10"/></svg></div>
          <h3 class="tile__t" data-i18n><span data-ar>نصل إليك</span><span data-en>We come to you</span></h3>
          <p class="tile__d" data-i18n><span data-ar>البيت، العمل، أينما كانت سيارتك.</span><span data-en>Home, office, wherever you park.</span></p>
        </article>
        @endif
      </div>
    </div>
  </section>

  <!-- PLANS — multi-visit packages from the catalog -->
  @if($plans->isNotEmpty())
  <section id="plans">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>الباقات</span><span data-en>Plans</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>باقة تناسب <span class="hl">كل سيارة</span></span>
          <span data-en>A plan for <span class="hl">every car</span></span>
        </h2>
        <p class="section__sub" data-i18n="block">
          <span data-ar>غسلات بسعر أقل، ومواعيد أولوية — تحجزها في الوقت الذي يناسبك.</span>
          <span data-en>Cheaper washes and priority slots — book them whenever suits you.</span>
        </p>
      </div>

      <div class="plans">
        @foreach($plans as $i => $p)
        <div class="plan {{ $p->is_featured ? 'plan--featured' : '' }}" data-reveal data-delay="{{ $i % 3 }}">
          @if($p->is_featured)<div class="plan__pattern" aria-hidden="true"></div>@endif
          <span class="plan__badge">
            @if($p->is_featured)<x-i18n ar="الأكثر طلبًا" en="Most popular" />@else<x-i18n ar="باقة" en="Plan" />@endif
          </span>
          <h3 class="plan__name"><x-i18n :ar="$p->name_ar" :en="$p->name" /></h3>
          <p class="plan__for"><x-i18n :ar="$p->description_ar" :en="$p->description" /></p>
          <div class="tnum" style="font-family: var(--font-latin); font-size: 34px; font-weight: 800; margin: 6px 0 14px;">{{ $fmt($p->price) }} <small style="font-size:14px; font-weight:600;">SAR</small></div>
          <ul class="plan__list">
            @if($p->visits_count)<li>{!! $check !!}<span>{{ $p->visits_count }} <x-i18n ar="زيارات" en="visits" /></span></li>@endif
            @if($p->validity_days)<li>{!! $check !!}<span><x-i18n ar="صالحة لمدة" en="Valid for" /> {{ $p->validity_days }} <x-i18n ar="يوم" en="days" /></span></li>@endif
            <li>{!! $check !!}<span><x-i18n ar="أولوية في الحجز" en="Priority booking" /></span></li>
            <li>{!! $check !!}<span><x-i18n ar="الإضافات تُدفع عند الحجز" en="Add-ons paid per visit" /></span></li>
          </ul>
          <a href="/account/plans?subscribe={{ $p->id }}" class="btn {{ $p->is_featured ? 'btn--white' : 'btn--ghost' }}" data-i18n><span data-ar>اشترك</span><span data-en>Subscribe</span>{!! $arrow !!}</a>
        </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- WHY VELTO -->
  <section class="why" id="why" style="background: var(--bg);">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>ما يميزنا</span><span data-en>What sets us apart</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>راحتك وجودة خدمتك <span class="hl">أولويتنا</span></span>
          <span data-en>Your comfort and quality, <span class="hl">first</span></span>
        </h2>
      </div>
      <div class="why-grid">
        @foreach([
          ['تنظيف ولمعان','Clean & gloss','نتيجة تستحق النظر بعد كل زيارة.',"A finish you'll notice, every visit."],
          ['فريق محترف','Trained team','مدرّب ومُقيّم بعد كل زيارة.','Vetted and graded after each visit.'],
          ['سرعة وجودة','Speed & quality','التزام بالمواعيد في كل مرة.','Punctual, every single time.'],
          ['مواد آمنة','Safe products','لطيفة على طلاء سيارتك ودائمة.','Gentle on your paint, lasting shine.'],
          ['خدمة متنقلة','Mobile service','نصلك أينما كنت في الرياض.','We reach you anywhere in Riyadh.'],
          ['تجربة شخصية','Personal experience','عناية تُشعرك بالخصوصية والراحة.','Care that feels private and easy.'],
        ] as $k => $f)
        <div class="feat" data-reveal data-delay="{{ $k % 3 }}">
          <h4 class="feat__t"><x-i18n :ar="$f[0]" :en="$f[1]" /></h4>
          <p class="feat__d"><x-i18n :ar="$f[2]" :en="$f[3]" /></p>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- COVERAGE -->
  <section id="coverage-home">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>مناطق التغطية</span><span data-en>Coverage</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>نصلك في <span class="hl">الرياض</span></span>
          <span data-en>We come to you in <span class="hl">Riyadh</span></span>
        </h2>
      </div>
      <div class="chips" style="justify-content:center; max-width: 900px; margin: 0 auto;" data-reveal>
        @foreach($coverage->take(24) as $d)
        <span class="chip"><x-i18n :ar="$d->name_ar" :en="$d->name" /></span>
        @endforeach
        @if($coverage->count() > 24)
        <a href="/coverage" class="chip is-active">+{{ $coverage->count() - 24 }}</a>
        @endif
      </div>
      <p style="text-align:center; margin-top: 22px;"><a href="/coverage" class="btn btn--ghost" data-i18n><span data-ar>عرض الخريطة</span><span data-en>See the map</span>{!! $arrow !!}</a></p>
    </div>
  </section>

  <!-- APP DOWNLOAD -->
  <section class="app" id="app">
    <div class="container app__inner">
      <div data-reveal>
        <span class="eyebrow eyebrow--pill eyebrow--onpurple" data-i18n><span data-ar>تطبيق Velto</span><span data-en>The Velto app</span></span>
        <h2 class="app__title" data-i18n="block">
          <span data-ar>احجز، تابع، وقيّم — من جوالك</span>
          <span data-en>Book, track and rate — from your phone</span>
        </h2>
        <p class="app__sub" data-i18n="block">
          <span data-ar>نفس حسابك ونفس حجوزاتك — على الموقع وفي التطبيق. حمّل تطبيق Velto لتتبّع فريقنا وهو في طريقه إليك واستلام الإشعارات.</span>
          <span data-en>Same account, same bookings — on the web and in the app. Get the app to track your detailer on the way and receive notifications.</span>
        </p>
        <div class="store">
          <a href="https://apps.apple.com/app/id6762532453" target="_blank" rel="noopener" aria-label="App Store">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.4 12.6c0-2.5 2-3.6 2.1-3.7-1.2-1.7-3-1.9-3.6-2-1.5-.2-3 .9-3.8.9-.8 0-2-.9-3.3-.9-1.7 0-3.3 1-4.1 2.5-1.8 3.1-.5 7.6 1.3 10.1.9 1.2 1.9 2.6 3.2 2.6 1.3-.1 1.8-.8 3.3-.8s2 .8 3.3.8c1.4 0 2.3-1.3 3.1-2.5 1-1.4 1.4-2.8 1.4-2.9 0 0-2.8-1.1-2.9-4.1zM14 5.3c.7-.8 1.1-2 1-3.1-1 0-2.2.7-2.9 1.5-.6.7-1.2 1.9-1 3 1.1.1 2.2-.6 2.9-1.4z"/></svg>
            <span><small data-i18n><span data-ar>حمّله من</span><span data-en>Download on the</span></small><strong>App Store</strong></span>
          </a>
          <a href="https://play.google.com/store/apps/details?id=sa.velto.velto_customer" target="_blank" rel="noopener" aria-label="Google Play">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3.6 2.3l10.4 9.7-10.4 9.7c-.3-.2-.6-.6-.6-1.1V3.4c0-.5.3-.9.6-1.1zM15.3 13.2l2.7 2.5-11.2 6.4 8.5-8.9zm0-2.4L6.8 1.9l11.2 6.4-2.7 2.5zm1.1 1.2l3.2-1.8c.9-.5.9-1.4 0-1.9l-3.2-1.8-2.9 2.7 2.9 2.8z"/></svg>
            <span><small data-i18n><span data-ar>احصل عليه من</span><span data-en>Get it on</span></small><strong>Google Play</strong></span>
          </a>
        </div>
      </div>
      <div class="app__art" data-reveal data-delay="1">
        <div class="app__phone">
          <span class="app__notch"></span>
          <span class="word">Velto{!! $spark !!}</span>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ — from the admin -->
  @if($faqs->isNotEmpty())
  <section id="faq" style="background: var(--bg);">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>الأسئلة الشائعة</span><span data-en>FAQ</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>الإجابات التي <span class="hl">تبحث عنها</span></span>
          <span data-en>Answers you're <span class="hl">looking for</span></span>
        </h2>
      </div>
      @include('site.partials.faq-list', ['faqs' => $faqs])
      <p style="text-align:center; margin-top: 8px;"><a href="/faq" class="btn btn--ghost" data-i18n><span data-ar>كل الأسئلة</span><span data-en>All questions</span>{!! $arrow !!}</a></p>
    </div>
  </section>
  @endif

  <!-- CONTACT -->
  <section id="contact">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>تواصل معنا</span><span data-en>Get in touch</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>جاهزون <span class="hl">لخدمتك</span></span>
          <span data-en>We're <span class="hl">here to help</span></span>
        </h2>
      </div>

      <div class="contact-grid">
        <div class="contact-card contact-card--info" data-reveal>
          <div class="cc__pattern" aria-hidden="true"></div>
          <h3 data-i18n><span data-ar>معلومات التواصل</span><span data-en>Contact details</span></h3>
          <p data-i18n="block">
            <span data-ar>فريقنا في الرياض مستعد لخدمتك كل أيام الأسبوع.</span>
            <span data-en>Our Riyadh team is ready for you, every day of the week.</span>
          </p>
          <ul class="contact-list">
            <li class="tnum"><span><a href="tel:{{ $phone }}" dir="ltr">{{ $phone }}</a><small data-i18n><span data-ar>اتصل بنا مباشرة</span><span data-en>Call us directly</span></small></span></li>
            <li class="tnum"><span><a href="https://wa.me/{{ $whatsapp }}" dir="ltr">+{{ $whatsapp }}</a><small data-i18n><span data-ar>واتساب</span><span data-en>WhatsApp</span></small></span></li>
            <li><span><a href="mailto:{{ $email }}">{{ $email }}</a><small data-i18n><span data-ar>البريد الإلكتروني</span><span data-en>Email us</span></small></span></li>
            <li><span><a href="/account/support" data-i18n><span data-ar>مركز المساعدة</span><span data-en>Help Center</span></a><small data-i18n><span data-ar>تذاكر واقتراحات</span><span data-en>Tickets & suggestions</span></small></span></li>
          </ul>
        </div>

        {{-- The form hands the message to WhatsApp — the channel the team actually answers on. --}}
        <form class="contact-card" data-reveal data-delay="1" x-data="{ name: '', msg: '' }"
              @submit.prevent="window.open('https://wa.me/{{ $whatsapp }}?text=' + encodeURIComponent((name ? name + ': ' : '') + msg), '_blank')">
          <h3 data-i18n><span data-ar>أرسل لنا رسالة</span><span data-en>Send us a message</span></h3>
          <p style="color: var(--muted); margin: 0;" data-i18n="block">
            <span data-ar>تصلنا رسالتك على واتساب ونرد عليك في أقرب وقت.</span>
            <span data-en>Your message reaches us on WhatsApp and we reply as soon as we can.</span>
          </p>
          <div class="form">
            <div class="field">
              <label data-i18n><span data-ar>الاسم</span><span data-en>Full name</span></label>
              <input type="text" class="input" x-model="name" required>
            </div>
            <div class="field">
              <label data-i18n><span data-ar>رسالتك</span><span data-en>Your message</span></label>
              <textarea rows="4" class="textarea" x-model="msg" required></textarea>
            </div>
            <button type="submit" class="btn btn--primary form__submit" data-i18n>
              <span data-ar>إرسال عبر واتساب</span><span data-en>Send on WhatsApp</span>
              {!! $arrow !!}
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>

</main>
@endsection
