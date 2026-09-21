@extends('site.layout')
@php $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.'); @endphp
@section('content')
<main class="page">
  <section class="page__hero">
    <div class="container">
      <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>الباقات</span><span data-en>Plans</span></span>
      <h1 class="page__title" data-i18n="block"><span data-ar>غسلات بسعر أقل، ومواعيد أولوية</span><span data-en>Cheaper washes, priority slots</span></h1>
      <p class="page__sub" data-i18n="block"><span data-ar>اشترك مرة واحجز زياراتك في الوقت الذي يناسبك. تُخصم الزيارات من باقتك تلقائياً.</span><span data-en>Subscribe once and book your visits whenever suits you. Visits are deducted from your plan automatically.</span></p>
    </div>
  </section>
  <section class="page__body">
    <div class="container">
      @if($plans->isEmpty())
        <p class="empty"><x-i18n ar="لا توجد باقات متاحة حالياً." en="No plans available right now." /></p>
      @else
      <div class="grid grid--3">
        @foreach($plans as $p)
        <article class="card {{ $p->is_featured ? 'card--sel is-active' : '' }}" data-reveal>
          @if($p->is_featured)<span class="pill"><x-i18n ar="الأكثر طلبًا" en="Most popular" /></span>@endif
          <h2 class="h3" style="margin-top: 10px;"><x-i18n :ar="$p->name_ar" :en="$p->name" /></h2>
          <p class="muted" style="margin: 8px 0 0;"><x-i18n :ar="$p->description_ar" :en="$p->description" block /></p>
          <div class="price tnum" style="font-size: 36px; margin: 14px 0;">{{ $fmt($p->price) }}<small>SAR</small></div>
          <dl class="kv">
            @if($p->visits_count)<dt><x-i18n ar="الزيارات" en="Visits" /></dt><dd class="tnum">{{ $p->visits_count }}</dd>@endif
            @if($p->validity_days)<dt><x-i18n ar="الصلاحية" en="Validity" /></dt><dd class="tnum">{{ $p->validity_days }} <x-i18n ar="يوم" en="days" /></dd>@endif
            <dt><x-i18n ar="مدة الزيارة" en="Visit length" /></dt><dd class="tnum">{{ $p->duration_minutes }} <x-i18n ar="دقيقة" en="min" /></dd>
          </dl>
          <div style="margin-top: 20px;">
            <a href="/account/plans?subscribe={{ $p->id }}" class="btn btn--primary btn--block" data-i18n><span data-ar>اشترك الآن</span><span data-en>Subscribe</span>{!! $arrow !!}</a>
          </div>
        </article>
        @endforeach
      </div>
      @endif
    </div>
  </section>
</main>
@endsection
