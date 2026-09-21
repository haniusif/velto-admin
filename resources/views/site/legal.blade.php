@extends('site.layout')
@section('content')
<main class="page">
  <section class="page__hero">
    <div class="container">
      <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>قانوني</span><span data-en>Legal</span></span>
      <h1 class="page__title"><x-i18n :ar="$page->title_ar" :en="$page->title" block /></h1>
      @if($page->version)<p class="page__sub small"><x-i18n ar="الإصدار" en="Version" /> {{ $page->version }} · {{ $page->updated_at?->format('Y-m-d') }}</p>@endif
    </div>
  </section>
  <section class="page__body">
    <div class="container">
      <div class="card prose"><x-i18n :ar="$page->body_ar" :en="$page->body" block /></div>
    </div>
  </section>
</main>
@endsection
