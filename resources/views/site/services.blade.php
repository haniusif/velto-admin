@extends('site.layout')
@php $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.'); @endphp
@section('content')
<main class="page">
  <section class="page__hero">
    <div class="container">
      <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></span>
      <h1 class="page__title" data-i18n="block"><span data-ar>اختر ما تحتاجه سيارتك</span><span data-en>Pick what your car needs</span></h1>
      <p class="page__sub" data-i18n="block"><span data-ar>السعر المعروض للسيارات الصغيرة؛ يُضاف فرق بسيط للأحجام الأكبر ويظهر لك قبل التأكيد.</span><span data-en>Prices shown are for small cars; a small size difference for larger vehicles is shown before you confirm.</span></p>
    </div>
  </section>
  <section class="page__body">
    <div class="container">
      <div class="grid grid--2">
        @foreach($services as $s)
        <article class="card" data-reveal>
          <div class="row row--between">
            <h2 class="h3"><x-i18n :ar="$s->name_ar" :en="$s->name" /></h2>
            <span class="price tnum">{{ $fmt($s->price) }}<small>SAR</small></span>
          </div>
          <p class="muted" style="margin: 10px 0 0;"><x-i18n :ar="$s->description_ar" :en="$s->description" block /></p>
          <p class="small muted" style="margin: 8px 0 0;">⏱ {{ $s->duration_minutes }} <x-i18n ar="دقيقة" en="min" /></p>
          @if($s->addOns->isNotEmpty())
          <div class="divider"></div>
          <p class="h4" style="margin-bottom: 8px;"><x-i18n ar="إضافات متاحة" en="Available add-ons" /></p>
          <div class="chips">
            @foreach($s->addOns as $a)
            <span class="chip"><x-i18n :ar="$a->name_ar" :en="$a->name" /> · <span class="tnum">+{{ $fmt($a->extra_price) }}</span></span>
            @endforeach
          </div>
          @endif
          <div style="margin-top: 20px;">
            <a href="/book?service={{ $s->id }}" class="btn btn--primary" data-i18n><span data-ar>احجز الآن</span><span data-en>Book now</span>{!! $arrow !!}</a>
          </div>
        </article>
        @endforeach
      </div>
    </div>
  </section>
</main>
@endsection
