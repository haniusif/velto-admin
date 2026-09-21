<div class="faq" x-data="{ open: null }">
  @foreach($faqs as $i => $f)
  <div class="faq__item" :class="{ 'is-open': open === {{ $f->id }} }">
    <button class="faq__q" type="button" @click="open = open === {{ $f->id }} ? null : {{ $f->id }}">
      <span class="faq__num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
      <span class="faq__q-text"><x-i18n :ar="$f->question_ar" :en="$f->question" /></span>
      <span class="faq__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
    </button>
    <div class="faq__a"><div class="faq__a-inner"><x-i18n :ar="$f->answer_ar" :en="$f->answer" block /></div></div>
  </div>
  @endforeach
</div>
