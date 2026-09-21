@extends('site.layout')
@section('content')
<main class="page">
  <section class="page__hero">
    <div class="container">
      <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>الأسئلة الشائعة</span><span data-en>FAQ</span></span>
      <h1 class="page__title" data-i18n="block"><span data-ar>الإجابات التي تبحث عنها</span><span data-en>Answers you're looking for</span></h1>
    </div>
  </section>
  <section class="page__body">
    <div class="container">
      @if($faqs->isEmpty())
        <p class="empty"><x-i18n ar="لا توجد أسئلة بعد." en="No questions yet." /></p>
      @else
        @include('site.partials.faq-list', ['faqs' => $faqs])
      @endif
      <p style="text-align:center; margin-top: 28px;" class="muted"><x-i18n ar="لم تجد إجابتك؟" en="Didn't find your answer?" /> <a href="https://wa.me/{{ $support['support.whatsapp'] ?? '966559809687' }}" style="color: var(--purple); font-weight: 700;">WhatsApp</a></p>
    </div>
  </section>
</main>
@endsection
