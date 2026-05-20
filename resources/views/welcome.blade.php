<!doctype html>
<html lang="ar" dir="rtl" data-lang="ar">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Velto · فيلتو — الخيار الذكي لغسيل سيارتك</title>
<meta name="description" content="فيلتو — خدمة عناية بالسيارات متنقلة. نصلك أينما كنت في الرياض. تجربة شخصية وجودة تفوق توقعاتك.">
<meta name="theme-color" content="#8863E5">
<link rel="icon" href="/img/logo-velto.png">

<style>
  /* ---------- Fonts ---------- */
  @font-face { font-family: 'Poppins'; src: url('/fonts/poppins/Poppins-Regular.ttf')   format('truetype'); font-weight: 400; font-style: normal; font-display: swap; }
  @font-face { font-family: 'Poppins'; src: url('/fonts/poppins/Poppins-Italic.ttf')    format('truetype'); font-weight: 400; font-style: italic; font-display: swap; }
  @font-face { font-family: 'Poppins'; src: url('/fonts/poppins/Poppins-Medium.ttf')    format('truetype'); font-weight: 500; font-style: normal; font-display: swap; }
  @font-face { font-family: 'Poppins'; src: url('/fonts/poppins/Poppins-SemiBold.ttf')  format('truetype'); font-weight: 600; font-style: normal; font-display: swap; }
  @font-face { font-family: 'Poppins'; src: url('/fonts/poppins/Poppins-Bold.ttf')      format('truetype'); font-weight: 700; font-style: normal; font-display: swap; }
  @font-face { font-family: 'Poppins'; src: url('/fonts/poppins/Poppins-ExtraBold.ttf') format('truetype'); font-weight: 800; font-style: normal; font-display: swap; }

  @font-face { font-family: 'GE SS Two'; src: url('/fonts/gess/GESSTwo-Light.otf')  format('opentype'); font-weight: 300; font-display: swap; }
  @font-face { font-family: 'GE SS Two'; src: url('/fonts/gess/GESSTwo-Medium.otf') format('opentype'); font-weight: 500; font-display: swap; }
  @font-face { font-family: 'GE SS Two'; src: url('/fonts/gess/GESSTwo-Bold.otf')   format('opentype'); font-weight: 700; font-display: swap; }

  /* ---------- Tokens ---------- */
  :root {
    --purple:    #8863E5;
    --purple-2:  #7a55da;
    --lavender:  #B38BEE;
    --lilac:     #CBB5F3;
    --mint:      #C9E3DA;
    --mint-ink:  #2D5A48;
    --bg:        #FAFAFB;
    --surface:   #FFFFFF;
    --fg:        #171127;
    --muted:     #6B6580;
    --border:    #EDEAF6;
    --ink:       #0E0820;
    --max:       1180px;
    --pad:       clamp(20px, 4vw, 40px);

    /* Per-language family */
    --font-latin:  'Poppins', system-ui, sans-serif;
    --font-arabic: 'GE SS Two', 'Poppins', system-ui, sans-serif;
  }

  /* ---------- Reset ---------- */
  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }
  html { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility; }
  body {
    background: var(--bg);
    color: var(--fg);
    font-size: 16px;
    line-height: 1.7;
    overflow-x: hidden;
    font-family: var(--font-arabic);
  }
  html[data-lang="en"] body { font-family: var(--font-latin); line-height: 1.55; }
  img, svg { display: block; max-width: 100%; }
  a { color: inherit; text-decoration: none; }
  button { font: inherit; cursor: pointer; border: 0; background: transparent; color: inherit; }
  ::selection { background: var(--purple); color: #fff; }

  .tnum { font-variant-numeric: tabular-nums; }

  /* Per-language visibility */
  [data-i18n] [data-en], [data-i18n] [data-ar] { display: none; }
  html[data-lang="ar"] [data-i18n] [data-ar] { display: inline; }
  html[data-lang="en"] [data-i18n] [data-en] { display: inline; }
  /* block variants for paragraphs / list items */
  [data-i18n="block"] [data-en], [data-i18n="block"] [data-ar] { display: none; }
  html[data-lang="ar"] [data-i18n="block"] [data-ar] { display: block; }
  html[data-lang="en"] [data-i18n="block"] [data-en] { display: block; }

  /* ---------- Layout primitives ---------- */
  .container { max-width: var(--max); margin: 0 auto; padding: 0 var(--pad); }
  .eyebrow {
    font-size: 12px; font-weight: 600;
    color: var(--muted); letter-spacing: 0.18em; text-transform: uppercase;
    display: inline-flex; align-items: center; gap: 10px;
  }
  html[data-lang="ar"] .eyebrow { font-weight: 700; letter-spacing: 0.04em; }
  .eyebrow::before { content: ""; width: 24px; height: 1px; background: var(--purple); }

  /* ---------- Logo + wordmark ---------- */
  .logo-img { height: 34px; width: auto; }
  .word {
    font-family: var(--font-latin);
    font-weight: 800; font-style: italic;
    letter-spacing: -0.02em;
    display: inline-flex; align-items: center; gap: 0.05em;
    line-height: 1;
  }
  .word .spark {
    width: 0.42em; height: 0.42em;
    margin-bottom: 0.55em;
    color: currentColor; flex: none;
  }

  /* ---------- Nav ---------- */
  .nav {
    position: sticky; top: 0; z-index: 50;
    background: rgba(250,250,251,.78);
    backdrop-filter: saturate(140%) blur(10px);
    -webkit-backdrop-filter: saturate(140%) blur(10px);
    border-bottom: 1px solid transparent;
    transition: border-color .2s ease, background .2s ease;
  }
  .nav.is-scrolled { border-bottom-color: var(--border); }
  .nav__inner {
    display: flex; align-items: center; justify-content: space-between;
    height: 76px; gap: 16px;
  }
  .nav__brand { display: inline-flex; align-items: center; gap: 10px; }
  .nav__links { display: none; gap: 32px; font-size: 15px; }
  .nav__links a {
    color: var(--fg); opacity: 0.78; font-weight: 500;
    transition: opacity .15s ease, color .15s ease;
    position: relative;
  }
  .nav__links a:hover { opacity: 1; color: var(--purple); }
  .nav__right { display: inline-flex; align-items: center; gap: 10px; }

  .lang-toggle {
    display: inline-flex; align-items: center;
    border: 1px solid var(--border); border-radius: 999px;
    padding: 4px; background: #fff;
    font-family: var(--font-latin);
  }
  .lang-toggle button {
    padding: 6px 12px; border-radius: 999px;
    font-size: 12px; font-weight: 600;
    color: var(--muted);
    transition: background .15s ease, color .15s ease;
    letter-spacing: .04em;
  }
  .lang-toggle button.is-active { background: var(--fg); color: #fff; }

  .nav__cta {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--fg); color: #fff;
    padding: 11px 18px; border-radius: 999px;
    font-weight: 600; font-size: 14px;
    transition: transform .15s ease, background .15s ease;
  }
  .nav__cta:hover { background: var(--purple); transform: translateY(-1px); }

  .nav__menu { display: inline-flex; }
  .nav__menu svg { width: 22px; height: 22px; color: var(--fg); }
  @media (min-width: 980px) {
    .nav__links { display: inline-flex; }
    .nav__menu { display: none; }
  }

  /* ---------- Buttons ---------- */
  .btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 16px 28px; border-radius: 999px;
    font-weight: 600; font-size: 15px;
    transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease, border-color .15s ease;
    border: 1px solid transparent;
    white-space: nowrap;
  }
  .btn--primary {
    background: var(--purple); color: #fff;
    box-shadow: 0 1px 0 rgba(255,255,255,0.4) inset, 0 10px 24px -12px rgba(136, 99, 229, 0.55);
  }
  .btn--primary:hover { transform: translateY(-1px); background: var(--purple-2); }
  .btn--ghost {
    background: transparent; color: var(--fg);
    border-color: rgba(23,17,39,0.16);
  }
  .btn--ghost:hover { border-color: var(--fg); }

  /* arrows flip on RTL */
  html[dir="rtl"] .btn .arrow,
  html[dir="rtl"] .nav__cta .arrow,
  html[dir="rtl"] .band__cta .arrow { transform: scaleX(-1); }

  /* ---------- Hero ---------- */
  .hero {
    position: relative;
    padding: clamp(56px, 9vw, 120px) 0 clamp(72px, 11vw, 140px);
    overflow: hidden; isolation: isolate;
  }
  .hero__pattern {
    position: absolute; inset: 0;
    background: url('/img/pattern.png') center/720px repeat;
    opacity: 0.04;
    z-index: -2;
    mix-blend-mode: multiply;
    pointer-events: none;
  }
  .hero__giant {
    position: absolute; top: 18%;
    font-family: var(--font-latin);
    font-size: clamp(280px, 36vw, 520px);
    font-weight: 800; font-style: italic;
    letter-spacing: -0.05em;
    line-height: 0.85;
    color: var(--purple);
    opacity: 0.06;
    z-index: -1;
    pointer-events: none; user-select: none;
  }
  html[dir="ltr"] .hero__giant { right: -8%; }
  html[dir="rtl"] .hero__giant { left:  -8%; }

  .hero__grid {
    display: grid; grid-template-columns: 1fr;
    gap: clamp(32px, 5vw, 72px);
    align-items: end;
  }
  @media (min-width: 980px) { .hero__grid { grid-template-columns: 1.35fr 1fr; } }

  .hero__copy { max-width: 720px; }
  .hero__head {
    font-weight: 800; font-style: italic;
    font-size: clamp(40px, 6.6vw, 92px);
    line-height: 1.05;
    letter-spacing: -0.025em;
    margin: 22px 0 28px;
    color: var(--fg);
  }
  html[data-lang="ar"] .hero__head { font-style: normal; line-height: 1.25; letter-spacing: 0; }
  .hero__head em {
    font-style: italic; color: var(--purple);
    position: relative; white-space: nowrap;
  }
  html[data-lang="ar"] .hero__head em { font-style: normal; white-space: normal; }
  .hero__head em::after {
    content: ""; position: absolute; left: 0; right: 0; bottom: 0.04em;
    height: 0.18em; background: var(--mint);
    z-index: -1; border-radius: 2px;
  }
  .hero__sub {
    font-size: clamp(16px, 1.4vw, 19px);
    color: var(--muted); max-width: 56ch;
    margin-bottom: 36px;
  }
  .hero__ctas { display: flex; flex-wrap: wrap; gap: 12px; }

  /* Hero side card */
  .hero__card {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 30px 60px -40px rgba(23,17,39,0.18);
  }
  .hero__card-tag {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--mint); color: var(--mint-ink);
    padding: 6px 12px; border-radius: 999px;
    font-size: 12px; font-weight: 600; letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  .hero__card-tag .dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--mint-ink);
    box-shadow: 0 0 0 3px rgba(45,90,72,0.18);
  }
  .hero__card-h {
    margin: 18px 0 4px;
    font-size: 13px; font-weight: 600; color: var(--muted);
    text-transform: uppercase; letter-spacing: 0.14em;
  }
  .hero__slots { list-style: none; padding: 0; margin: 8px 0 18px; display: grid; gap: 10px; }
  .hero__slot {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    transition: border-color .15s ease, transform .15s ease;
  }
  .hero__slot:hover { border-color: var(--lilac); transform: translateY(-1px); }
  .hero__slot strong { font-weight: 700; font-size: 17px; }
  html[data-lang="en"] .hero__slot strong { font-style: italic; }
  .hero__slot small { color: var(--muted); font-size: 13px; }
  .hero__slot-pill {
    background: var(--bg); color: var(--purple);
    padding: 4px 10px; border-radius: 999px;
    font-family: var(--font-latin);
    font-size: 12px; font-weight: 600;
  }
  .hero__card-foot {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 6px; font-size: 13px; color: var(--muted);
  }
  .hero__card-foot a { color: var(--purple); font-weight: 600; }

  /* ---------- Sections ---------- */
  section { padding: clamp(72px, 10vw, 130px) 0; }
  .section__head {
    display: grid; gap: 18px;
    grid-template-columns: 1fr;
    margin-bottom: clamp(40px, 6vw, 72px);
    align-items: end;
  }
  @media (min-width: 880px) { .section__head { grid-template-columns: 1fr 1fr; } }
  .section__title {
    font-weight: 800;
    font-size: clamp(32px, 4.4vw, 54px);
    line-height: 1.15;
    letter-spacing: -0.02em;
    margin: 10px 0 0;
    max-width: 18ch;
  }
  html[data-lang="en"] .section__title { font-style: italic; line-height: 1.02; letter-spacing: -0.025em; }
  .section__sub { color: var(--muted); max-width: 50ch; }

  /* ---------- About ---------- */
  .about-grid { display: grid; grid-template-columns: 1fr; gap: 28px; }
  @media (min-width: 880px) { .about-grid { grid-template-columns: 1.15fr 1fr; gap: 48px; } }
  .about-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: clamp(24px, 3vw, 36px);
  }
  .about-card--vision {
    background: var(--ink);
    color: #fff;
    border-color: var(--ink);
    position: relative; overflow: hidden;
  }
  .about-card--vision::before {
    content: "";
    position: absolute; inset: 0;
    background: url('/img/pattern.png') center/520px repeat;
    opacity: 0.07; mix-blend-mode: screen; pointer-events: none;
  }
  .about-card h3 {
    font-weight: 800;
    font-size: clamp(22px, 2.4vw, 28px);
    margin: 0 0 14px;
    letter-spacing: -0.01em;
  }
  html[data-lang="en"] .about-card h3 { font-style: italic; }
  .about-card p { color: var(--muted); margin: 0 0 12px; }
  .about-card--vision p { color: rgba(255,255,255,0.78); }
  .about-card .word { color: var(--purple); }
  .about-card--vision .word { color: var(--lilac); }

  /* ---------- Services ---------- */
  .services-grid {
    display: grid; grid-template-columns: 1fr; gap: 16px;
  }
  @media (min-width: 720px) { .services-grid { grid-template-columns: 1fr 1fr; } }
  .svc {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: clamp(24px, 3vw, 32px);
    display: flex; flex-direction: column; gap: 14px;
    min-height: 280px;
    transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
  }
  .svc:hover {
    transform: translateY(-3px);
    border-color: var(--lilac);
    box-shadow: 0 30px 60px -40px rgba(23,17,39,0.22);
  }
  .svc__num {
    font-family: var(--font-latin);
    font-style: italic; font-weight: 700; font-size: 13px;
    color: var(--purple);
    font-variant-numeric: tabular-nums;
  }
  .svc__name {
    font-weight: 700; font-size: 26px; line-height: 1.2;
    letter-spacing: -0.01em;
  }
  html[data-lang="en"] .svc__name { font-style: italic; }
  .svc__desc { color: var(--muted); font-size: 15px; }
  .svc__cta {
    margin-top: auto;
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--purple); font-weight: 700;
    padding-top: 16px;
    border-top: 1px solid var(--border);
  }
  .svc__cta .arrow { transition: transform .2s ease; width: 16px; height: 16px; }
  .svc:hover .svc__cta .arrow { transform: translateX(4px); }
  html[dir="rtl"] .svc:hover .svc__cta .arrow { transform: translateX(-4px) scaleX(-1); }
  html[dir="rtl"] .svc__cta .arrow { transform: scaleX(-1); }

  /* ---------- What sets us apart ---------- */
  .why-bg {
    background: var(--surface);
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
  }
  .why-grid {
    display: grid; grid-template-columns: 1fr; gap: 0;
  }
  @media (min-width: 720px) { .why-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (min-width: 1080px) { .why-grid { grid-template-columns: repeat(5, 1fr); } }
  .why-cell {
    padding: 28px clamp(20px, 2.5vw, 32px);
    border-top: 1px solid var(--border);
    text-align: center;
  }
  @media (min-width: 720px) {
    .why-cell { border-top: 0; border-inline-start: 1px solid var(--border); }
    .why-cell:first-child { border-inline-start: 0; }
  }
  @media (min-width: 720px) and (max-width: 1079px) {
    .why-cell:nth-child(3) { border-inline-start: 0; border-top: 1px solid var(--border); }
    .why-cell:nth-child(4), .why-cell:nth-child(5) { border-top: 1px solid var(--border); }
  }
  .why-cell__icon {
    width: 56px; height: 56px;
    border-radius: 16px;
    background: rgba(136,99,229,0.08);
    color: var(--purple);
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 18px;
  }
  .why-cell__icon svg { width: 26px; height: 26px; }
  .why-cell__title {
    font-weight: 700; font-size: 17px; margin: 0 0 4px;
  }
  .why-cell__desc { color: var(--muted); font-size: 14px; }

  /* ---------- FAQ ---------- */
  .faq {
    border-top: 1px solid var(--border);
  }
  .faq__item {
    border-bottom: 1px solid var(--border);
    padding: 6px 0;
  }
  .faq__q {
    width: 100%;
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 18px; align-items: center;
    padding: 22px 0;
    text-align: start;
  }
  .faq__num {
    font-family: var(--font-latin);
    font-weight: 800; font-style: italic;
    color: var(--lavender);
    font-size: 16px; letter-spacing: -0.01em;
    font-variant-numeric: tabular-nums;
    min-width: 32px;
  }
  .faq__q-text {
    font-weight: 700; font-size: clamp(17px, 2vw, 20px);
    line-height: 1.4;
  }
  html[data-lang="en"] .faq__q-text { font-style: italic; letter-spacing: -0.01em; }
  .faq__icon {
    width: 32px; height: 32px; border-radius: 999px;
    border: 1px solid var(--border);
    display: inline-flex; align-items: center; justify-content: center;
    transition: transform .25s ease, background .25s ease, border-color .25s ease, color .25s ease;
    color: var(--fg);
  }
  .faq__icon svg { width: 14px; height: 14px; transition: transform .25s ease; }
  .faq__item.is-open .faq__icon { background: var(--purple); border-color: var(--purple); color: #fff; }
  .faq__item.is-open .faq__icon svg { transform: rotate(45deg); }
  .faq__a {
    max-height: 0; overflow: hidden;
    transition: max-height .35s ease, padding .35s ease;
  }
  .faq__a-inner {
    padding: 0 50px 22px 0;
    color: var(--muted);
    max-width: 60ch;
  }
  html[dir="rtl"] .faq__a-inner { padding: 0 0 22px 50px; }
  .faq__item.is-open .faq__a { max-height: 320px; }

  /* ---------- Contact ---------- */
  .contact-grid { display: grid; grid-template-columns: 1fr; gap: 28px; }
  @media (min-width: 980px) { .contact-grid { grid-template-columns: 1fr 1.2fr; gap: 48px; } }

  .contact-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: clamp(24px, 3vw, 36px);
  }
  .contact-list { list-style: none; padding: 0; margin: 18px 0 0; display: grid; gap: 14px; }
  .contact-list li { display: flex; align-items: center; gap: 14px; }
  .contact-list li .ico {
    width: 42px; height: 42px; border-radius: 12px;
    background: rgba(136,99,229,0.08);
    color: var(--purple);
    display: inline-flex; align-items: center; justify-content: center;
    flex: none;
  }
  .contact-list li .ico svg { width: 18px; height: 18px; }
  .contact-list li a, .contact-list li span { color: var(--fg); font-weight: 600; }
  .contact-list li small { display: block; color: var(--muted); font-weight: 500; font-size: 12px; margin-top: 2px; }
  .contact-list .tnum a { font-family: var(--font-latin); font-variant-numeric: tabular-nums; direction: ltr; unicode-bidi: isolate; }

  .form { display: grid; gap: 14px; margin-top: 22px; }
  .form__row { display: grid; gap: 14px; grid-template-columns: 1fr; }
  @media (min-width: 640px) { .form__row { grid-template-columns: 1fr 1fr; } }
  .field { display: grid; gap: 6px; }
  .field label {
    font-size: 12px; font-weight: 600; color: var(--muted);
    letter-spacing: 0.06em; text-transform: uppercase;
  }
  .field input, .field select, .field textarea {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 16px;
    color: var(--fg);
    font: inherit;
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  .field input:focus, .field select:focus, .field textarea:focus {
    outline: none;
    border-color: var(--purple);
    box-shadow: 0 0 0 4px rgba(136,99,229,0.12);
  }
  .field--phone { display: grid; grid-template-columns: auto 1fr; gap: 10px; }
  .field--phone .prefix {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 14px;
    font-family: var(--font-latin);
    font-weight: 600;
    color: var(--fg);
    direction: ltr; unicode-bidi: isolate;
    display: inline-flex; align-items: center;
  }
  .form__submit { justify-self: start; }

  /* ---------- CTA band ---------- */
  .band {
    position: relative;
    margin: 0;
    background: var(--purple);
    color: #fff;
    overflow: hidden; isolation: isolate;
  }
  .band::before {
    content: "";
    position: absolute; inset: 0;
    background: url('/img/pattern.png') center/640px repeat;
    opacity: 0.08;
    mix-blend-mode: screen;
    z-index: -1;
  }
  .band__inner {
    padding: clamp(60px, 9vw, 110px) 0;
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    align-items: center;
  }
  @media (min-width: 880px) { .band__inner { grid-template-columns: 1.3fr auto; } }
  .band__title {
    font-weight: 800;
    font-size: clamp(34px, 5vw, 64px);
    line-height: 1.1;
    letter-spacing: -0.02em;
    margin: 0 0 14px;
  }
  html[data-lang="en"] .band__title { font-style: italic; line-height: 0.95; letter-spacing: -0.03em; }
  .band__sub { color: rgba(255,255,255,0.85); font-size: 17px; max-width: 48ch; }
  .band__cta {
    display: inline-flex; gap: 12px; align-items: center;
    background: #fff; color: var(--purple);
    padding: 18px 28px;
    border-radius: 999px;
    font-weight: 700; font-size: 16px;
    transition: transform .15s ease;
    align-self: start;
  }
  html[data-lang="en"] .band__cta { font-style: italic; }
  .band__cta:hover { transform: translateY(-2px); }

  /* ---------- Footer ---------- */
  footer {
    background: var(--ink);
    color: rgba(255,255,255,0.7);
    padding: 80px 0 40px;
    font-size: 14px;
  }
  .footer__top {
    display: grid; grid-template-columns: 1fr; gap: 40px;
    padding-bottom: 48px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
  }
  @media (min-width: 880px) { .footer__top { grid-template-columns: 2fr 1fr 1fr 1fr; } }
  .footer__brand img { height: 36px; filter: brightness(0) invert(1); }
  .footer__tagline { margin-top: 18px; max-width: 32ch; line-height: 1.7; }
  .footer__h {
    color: #fff; font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.16em;
    margin: 0 0 14px;
  }
  .footer__list { list-style: none; padding: 0; margin: 0; display: grid; gap: 8px; }
  .footer__list a { transition: color .15s ease; }
  .footer__list a:hover { color: #fff; }
  .footer__bottom {
    display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between;
    padding-top: 28px;
    color: rgba(255,255,255,0.45);
    font-size: 12px;
  }

  /* ---------- Reveal on scroll ---------- */
  @media (prefers-reduced-motion: no-preference) {
    [data-reveal] { opacity: 0; transform: translateY(14px); transition: opacity .7s cubic-bezier(.2,.7,.2,1), transform .7s cubic-bezier(.2,.7,.2,1); }
    [data-reveal].is-in { opacity: 1; transform: none; }
  }
</style>
</head>
<body>

@php
  $spark = '<svg class="spark" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c.6 6 4.4 9.8 12 12-7.6 2.2-11.4 6-12 12-.6-6-4.4-9.8-12-12C7.6 9.8 11.4 6 12 0z" fill="currentColor"/></svg>';
  $arrow = '<svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12h14M13 5l7 7-7 7"/></svg>';
@endphp

<header class="nav" id="nav">
  <div class="container nav__inner">
    <a href="/" class="nav__brand" aria-label="Velto">
      <img src="/img/logo-velto.png" alt="Velto" class="logo-img">
    </a>

    <nav class="nav__links" aria-label="Primary">
      <a href="#hero" data-i18n><span data-ar>الرئيسية</span><span data-en>Home</span></a>
      <a href="#about" data-i18n><span data-ar>من نحن</span><span data-en>About</span></a>
      <a href="#services" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></a>
      <a href="#faq" data-i18n><span data-ar>الأسئلة الشائعة</span><span data-en>FAQ</span></a>
      <a href="#contact" data-i18n><span data-ar>تواصل معنا</span><span data-en>Contact</span></a>
    </nav>

    <div class="nav__right">
      <div class="lang-toggle" role="group" aria-label="Language">
        <button type="button" data-set-lang="ar" class="is-active">AR</button>
        <button type="button" data-set-lang="en">EN</button>
      </div>
      <a href="#contact" class="nav__cta" data-i18n>
        <span data-ar>تواصل معنا</span><span data-en>Contact us</span>
        {!! $arrow !!}
      </a>
      <button class="nav__menu" aria-label="Menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>
</header>

<main>

  <!-- HERO -->
  <section class="hero" id="hero">
    <div class="hero__pattern" aria-hidden="true"></div>
    <div class="hero__giant" aria-hidden="true">Velto</div>
    <div class="container hero__grid">
      <div class="hero__copy" data-reveal>
        <span class="eyebrow" data-i18n>
          <span data-ar>عناية بالسيارات · الرياض</span>
          <span data-en>Mobile car care · Riyadh</span>
        </span>
        <h1 class="hero__head" data-i18n="block">
          <span data-ar>الخيار الذكي<br>لغسيل <em>سيارتك</em></span>
          <span data-en>Car care that<br>comes <em>to&nbsp;you.</em></span>
        </h1>
        <p class="hero__sub" data-i18n="block">
          <span data-ar>تجربة متميزة وجودة تفوق توقعاتك. فريق Velto المدرب يصلك أينما كنت — في بيتك، في عملك، في يومك. بدون مواعيد طويلة، وبدون تعقيد.</span>
          <span data-en>A trained Velto detailer comes to your driveway, your office, your everyday. Same-day. No water mess. No waiting in lines.</span>
        </p>
        <div class="hero__ctas">
          <a href="#contact" class="btn btn--primary" data-i18n>
            <span data-ar>تواصل معنا</span>
            <span data-en>Contact us</span>
            {!! $arrow !!}
          </a>
          <a href="https://wa.me/966559809687" class="btn btn--ghost" data-i18n>
            <span data-ar>واتساب</span>
            <span data-en>WhatsApp</span>
            {!! $arrow !!}
          </a>
        </div>
      </div>

      <aside class="hero__card" data-reveal>
        <span class="hero__card-tag" data-i18n>
          <span class="dot"></span>
          <span data-ar>نرد خلال دقائق</span><span data-en>We reply in minutes</span>
        </span>
        <h2 class="hero__card-h" data-i18n>
          <span data-ar>تواصل معنا مباشرة</span>
          <span data-en>Reach us directly</span>
        </h2>
        <ul class="hero__slots tnum">
          <li class="hero__slot">
            <div>
              <strong data-i18n><span data-ar>واتساب</span><span data-en>WhatsApp</span></strong><br>
              <small dir="ltr" style="unicode-bidi: isolate;">+966 55 980 9687</small>
            </div>
            <a href="https://wa.me/966559809687" class="hero__slot-pill" data-i18n><span data-ar>محادثة</span><span data-en>Chat</span></a>
          </li>
          <li class="hero__slot">
            <div>
              <strong data-i18n><span data-ar>اتصال</span><span data-en>Call</span></strong><br>
              <small dir="ltr" style="unicode-bidi: isolate;">+966 55 980 9687</small>
            </div>
            <a href="tel:+966559809687" class="hero__slot-pill" data-i18n><span data-ar>اتصل</span><span data-en>Dial</span></a>
          </li>
          <li class="hero__slot">
            <div>
              <strong data-i18n><span data-ar>بريد إلكتروني</span><span data-en>Email</span></strong><br>
              <small>info@velto.sa</small>
            </div>
            <a href="mailto:info@velto.sa" class="hero__slot-pill" data-i18n><span data-ar>راسلنا</span><span data-en>Send</span></a>
          </li>
        </ul>
        <div class="hero__card-foot">
          <span data-i18n>
            <span data-ar>كل أيام الأسبوع</span>
            <span data-en>Every day of the week</span>
          </span>
          <a href="#contact" data-i18n>
            <span data-ar>نموذج التواصل ←</span>
            <span data-en>Contact form →</span>
          </a>
        </div>
      </aside>
    </div>
  </section>

  <!-- ABOUT -->
  <section id="about">
    <div class="container">
      <div class="section__head">
        <div>
          <span class="eyebrow" data-i18n><span data-ar>عن فيلتو</span><span data-en>About Velto</span></span>
          <h2 class="section__title" data-i18n="block">
            <span data-ar>عناية شخصية،<br>لا مجرد غسيل سيارة.</span>
            <span data-en>Personal care,<br>not just a car wash.</span>
          </h2>
        </div>
        <p class="section__sub" data-i18n="block">
          <span data-ar>نمنح كل عميل شعورًا بالخصوصية والرعاية التي تستحقها سيارته، مع تقديم تجربة مميزة تعكس قيم الجودة والثقة.</span>
          <span data-en>Velto is built for individuals. We give every customer the sense of privacy and care their car deserves — with attention to every detail.</span>
        </p>
      </div>

      <div class="about-grid">
        <div class="about-card" data-reveal>
          <h3 data-i18n><span data-ar>من نحن</span><span data-en>Who we are</span></h3>
          <p data-i18n="block">
            <span data-ar><span class="word">Velto{!! $spark !!}</span> شركة متخصصة في تقديم حلول العناية بالسيارات للأفراد، تركّز على توفير تجربة مريحة وشخصية تُلائم احتياجات كل عميل.</span>
            <span data-en><span class="word">Velto{!! $spark !!}</span> is a Saudi company specialised in car-care solutions for individuals — built around comfort, personalisation, and a quality-first experience.</span>
          </p>
          <p data-i18n="block">
            <span data-ar>نسعى إلى بناء علاقة موثوقة مع عملائنا من خلال تجربة سلسة تعتمد على الجودة والاهتمام، وفريق عمل مدرّب يضمن رضاك الكامل.</span>
            <span data-en>We're building a trusted relationship with every customer through a seamless service, grounded in quality and a fully trained team.</span>
          </p>
        </div>

        <div class="about-card about-card--vision" data-reveal>
          <h3 data-i18n><span data-ar>رؤيتنا</span><span data-en>Our vision</span></h3>
          <p data-i18n="block">
            <span data-ar>تقديم تجربة شخصية ومريحة في مجال العناية بالسيارات، من خلال التركيز على جودة الخدمة والاهتمام بأدق التفاصيل لتلبية احتياجات كل عميل وتعزيز راحته ورضاه.</span>
            <span data-en>To deliver a personal, comfortable car-care experience by focusing on service quality and attention to every detail — for every customer.</span>
          </p>
          <a href="#contact" class="btn btn--primary" style="margin-top: 18px; background: #fff; color: var(--purple); box-shadow: none;" data-i18n>
            <span data-ar>تواصل معنا</span><span data-en>Contact us</span>
            {!! $arrow !!}
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- SERVICES -->
  <section id="services" style="background: var(--bg);">
    <div class="container">
      <div class="section__head">
        <div>
          <span class="eyebrow" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></span>
          <h2 class="section__title" data-i18n="block">
            <span data-ar>اختر ما تحتاجه سيارتك.</span>
            <span data-en>Pick what your car needs.</span>
          </h2>
        </div>
        <p class="section__sub" data-i18n="block">
          <span data-ar>كل زيارة يتولاها فريق Velto المدرّب، بأدواته ومواده الخاصة. أنت لا توفّر شيئًا — فقط افتح التطبيق وانتظرنا.</span>
          <span data-en>Every visit is run by a trained Velto detailer with their own kit, water, and waterless system. You don't supply a thing.</span>
        </p>
      </div>

      <div class="services-grid">
        <article class="svc" data-reveal>
          <span class="svc__num">01.</span>
          <h3 class="svc__name" data-i18n><span data-ar>غسيل خارجي سريع</span><span data-en>Express exterior wash</span></h3>
          <p class="svc__desc" data-i18n="block">
            <span data-ar>تنظيف شامل لجسم السيارة الخارجي باستخدام مواد عالية الجودة تضمن لمعان السيارة وإزالة الأتربة والأوساخ بسرعة وكفاءة.</span>
            <span data-en>A full exterior clean using premium products — gloss finish, dust and grime removed quickly and efficiently.</span>
          </p>
          <a href="#contact" class="svc__cta" data-i18n>
            <span data-ar>تواصل معنا</span><span data-en>Contact us</span>
            {!! $arrow !!}
          </a>
        </article>

        <article class="svc" data-reveal>
          <span class="svc__num">02.</span>
          <h3 class="svc__name" data-i18n><span data-ar>تنظيف متكامل</span><span data-en>Full detail</span></h3>
          <p class="svc__desc" data-i18n="block">
            <span data-ar>يشمل تنظيف السيارة من الخارج والداخل بدقة، بما في ذلك المقاعد والأرضيات والطبلون، مع الاهتمام بالتفاصيل لإعادة الانتعاش والنظافة.</span>
            <span data-en>Inside and out — seats, floors, dashboard — with the attention to detail that brings every surface back to life.</span>
          </p>
          <a href="#contact" class="svc__cta" data-i18n>
            <span data-ar>تواصل معنا</span><span data-en>Contact us</span>
            {!! $arrow !!}
          </a>
        </article>
      </div>
    </div>
  </section>

  <!-- WHY US -->
  <section class="why-bg">
    <div class="container">
      <div class="section__head">
        <div>
          <span class="eyebrow" data-i18n><span data-ar>ما يميزنا</span><span data-en>What sets us apart</span></span>
          <h2 class="section__title" data-i18n="block">
            <span data-ar>لأن راحتك وجودة خدمتك أولويتنا.</span>
            <span data-en>Your comfort and quality, first.</span>
          </h2>
        </div>
        <p class="section__sub" data-i18n="block">
          <span data-ar>كل تفصيل في خدمتنا مصمم ليمنحك الشعور الذي تستحقه سيارتك — من المنتجات الآمنة إلى الفريق الذي يصلك في الوقت.</span>
          <span data-en>Every detail of the service is built around the experience your car deserves — from safe products to a team that arrives on time.</span>
        </p>
      </div>

      <div class="why-grid">
        <div class="why-cell" data-reveal>
          <div class="why-cell__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2 5 5 .8-3.6 3.5.9 5L12 13.8 7.7 16.3l.9-5L5 7.8 10 7z"/></svg>
          </div>
          <h4 class="why-cell__title" data-i18n><span data-ar>تنظيف ولمعان</span><span data-en>Clean &amp; gloss</span></h4>
          <p class="why-cell__desc" data-i18n><span data-ar>نتيجة تستحق النظر</span><span data-en>A finish you'll notice</span></p>
        </div>
        <div class="why-cell" data-reveal>
          <div class="why-cell__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
          <h4 class="why-cell__title" data-i18n><span data-ar>فريق محترف</span><span data-en>Trained team</span></h4>
          <p class="why-cell__desc" data-i18n><span data-ar>مدرّب ومُقيّم بعد كل زيارة</span><span data-en>Vetted, graded each visit</span></p>
        </div>
        <div class="why-cell" data-reveal>
          <div class="why-cell__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          </div>
          <h4 class="why-cell__title" data-i18n><span data-ar>سرعة وجودة</span><span data-en>Speed &amp; quality</span></h4>
          <p class="why-cell__desc" data-i18n><span data-ar>التزام بالمواعيد دائمًا</span><span data-en>Always on time</span></p>
        </div>
        <div class="why-cell" data-reveal>
          <div class="why-cell__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
          <h4 class="why-cell__title" data-i18n><span data-ar>مواد آمنة</span><span data-en>Safe products</span></h4>
          <p class="why-cell__desc" data-i18n><span data-ar>على طلاء سيارتك</span><span data-en>Gentle on your paint</span></p>
        </div>
        <div class="why-cell" data-reveal>
          <div class="why-cell__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h13l3-3v6l-3-3M3 6h10M3 18h10"/></svg>
          </div>
          <h4 class="why-cell__title" data-i18n><span data-ar>خدمة متنقلة</span><span data-en>Mobile service</span></h4>
          <p class="why-cell__desc" data-i18n><span data-ar>نصلك أينما كنت</span><span data-en>We come to you</span></p>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq">
    <div class="container">
      <div class="section__head">
        <div>
          <span class="eyebrow" data-i18n><span data-ar>الأسئلة الشائعة</span><span data-en>FAQ</span></span>
          <h2 class="section__title" data-i18n="block">
            <span data-ar>الإجابات التي تبحث عنها.</span>
            <span data-en>Answers you're looking for.</span>
          </h2>
        </div>
        <p class="section__sub" data-i18n="block">
          <span data-ar>سؤال آخر؟ تواصل معنا مباشرة وسنجيبك خلال دقائق.</span>
          <span data-en>Got another question? Reach out — we usually reply in minutes.</span>
        </p>
      </div>

      <div class="faq" id="faqList">
        <div class="faq__item">
          <button class="faq__q">
            <span class="faq__num">01</span>
            <span class="faq__q-text" data-i18n><span data-ar>هل أحتاج إلى مكان مخصص لغسيل السيارة؟</span><span data-en>Do I need a special place to wash the car?</span></span>
            <span class="faq__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            </span>
          </button>
          <div class="faq__a"><div class="faq__a-inner" data-i18n="block">
            <span data-ar>لا. فريقنا يصل بأدواته الخاصة ونظام الغسيل اللامائي في الغالب — أي موقف عادي يكفي. لا حاجة لمصدر ماء ولا لتصريف.</span>
            <span data-en>No. Our team arrives with their own kit and waterless system — a regular parking spot is enough. No water source or drainage required.</span>
          </div></div>
        </div>

        <div class="faq__item">
          <button class="faq__q">
            <span class="faq__num">02</span>
            <span class="faq__q-text" data-i18n><span data-ar>هل المواد المستخدمة آمنة على طلاء السيارة؟</span><span data-en>Are the products safe for the paint?</span></span>
            <span class="faq__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            </span>
          </button>
          <div class="faq__a"><div class="faq__a-inner" data-i18n="block">
            <span data-ar>نعم. نستخدم مواد متخصصة ومُختبرة، آمنة تمامًا على طلاء السيارة وعلى الأسطح الداخلية. كل منتج يُقيَّم قبل اعتماده.</span>
            <span data-en>Yes. We use specialised, tested products that are fully safe for paint and interior surfaces. Every product is reviewed before approval.</span>
          </div></div>
        </div>

        <div class="faq__item">
          <button class="faq__q">
            <span class="faq__num">03</span>
            <span class="faq__q-text" data-i18n><span data-ar>هل تلتزمون بالمواعيد؟</span><span data-en>Do you keep to your schedule?</span></span>
            <span class="faq__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            </span>
          </button>
          <div class="faq__a"><div class="faq__a-inner" data-i18n="block">
            <span data-ar>نعم، الالتزام بالموعد جزء أساسي من الخدمة. تصلك إشعارات عند انطلاق الفريق وعند وصوله، وفي حال تأخر استثنائي يُعوَّض الوقت.</span>
            <span data-en>Yes — punctuality is core to the service. You'll be notified when the team is on its way and when they arrive. Any rare delay is compensated.</span>
          </div></div>
        </div>

        <div class="faq__item">
          <button class="faq__q">
            <span class="faq__num">04</span>
            <span class="faq__q-text" data-i18n><span data-ar>كيف أقيّم جودة خدمتكم؟</span><span data-en>How do I rate the quality of the service?</span></span>
            <span class="faq__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            </span>
          </button>
          <div class="faq__a"><div class="faq__a-inner" data-i18n="block">
            <span data-ar>بعد كل زيارة يصلك تقييم سريع داخل التطبيق. ملاحظاتك تذهب مباشرة إلى فريق الجودة وتؤثر في تقييم المتخصص.</span>
            <span data-en>After every visit you'll get a quick in-app rating. Your feedback goes straight to our quality team and affects the detailer's grading.</span>
          </div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- CONTACT -->
  <section id="contact" style="background: var(--bg);">
    <div class="container">
      <div class="section__head">
        <div>
          <span class="eyebrow" data-i18n><span data-ar>تواصل معنا</span><span data-en>Get in touch</span></span>
          <h2 class="section__title" data-i18n="block">
            <span data-ar>نحن هنا للإجابة.</span>
            <span data-en>We're here to help.</span>
          </h2>
        </div>
        <p class="section__sub" data-i18n="block">
          <span data-ar>اترك بياناتك وسيتواصل معك فريقنا في أقرب وقت.</span>
          <span data-en>Leave your details and our team will get back to you shortly.</span>
        </p>
      </div>

      <div class="contact-grid">
        <div class="contact-card" data-reveal>
          <h3 style="margin: 0 0 8px; font-weight: 700; font-size: 22px;" data-i18n>
            <span data-ar>معلومات التواصل</span>
            <span data-en>Contact details</span>
          </h3>
          <p style="color: var(--muted); margin: 0;" data-i18n="block">
            <span data-ar>فريقنا في الرياض مستعد لخدمتك كل أيام الأسبوع.</span>
            <span data-en>Our Riyadh team is ready for you, every day of the week.</span>
          </p>

          <ul class="contact-list">
            <li>
              <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
              <span>
                <span data-i18n><span data-ar>الرياض، المملكة العربية السعودية</span><span data-en>Riyadh, Saudi Arabia</span></span>
                <small data-i18n><span data-ar>المملكة العربية السعودية</span><span data-en>Kingdom of Saudi Arabia</span></small>
              </span>
            </li>
            <li class="tnum">
              <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg></span>
              <span>
                <a href="tel:+966559809687">+966 55 980 9687</a>
                <small data-i18n><span data-ar>اتصل بنا مباشرة</span><span data-en>Call us directly</span></small>
              </span>
            </li>
            <li class="tnum">
              <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg></span>
              <span>
                <a href="https://wa.me/966559809687">+966 55 980 9687</a>
                <small data-i18n><span data-ar>واتساب</span><span data-en>WhatsApp</span></small>
              </span>
            </li>
            <li>
              <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg></span>
              <span>
                <a href="mailto:info@velto.sa">info@velto.sa</a>
                <small data-i18n><span data-ar>البريد الإلكتروني</span><span data-en>Email us</span></small>
              </span>
            </li>
          </ul>
        </div>

        <form class="contact-card" data-reveal onsubmit="event.preventDefault(); this.querySelector('.form__success').style.display='block'; this.querySelector('.form__submit').setAttribute('disabled','');">
          <h3 style="margin: 0 0 8px; font-weight: 700; font-size: 22px;" data-i18n>
            <span data-ar>أرسل لنا رسالة</span>
            <span data-en>Send us a message</span>
          </h3>
          <p style="color: var(--muted); margin: 0;" data-i18n="block">
            <span data-ar>اترك بياناتك وسنرد عليك في أقرب وقت.</span>
            <span data-en>Leave your details and we'll reply as soon as we can.</span>
          </p>

          <div class="form">
            <div class="form__row">
              <div class="field">
                <label data-i18n><span data-ar>الاسم</span><span data-en>Full name</span></label>
                <input type="text" name="name" required>
              </div>
              <div class="field">
                <label data-i18n><span data-ar>الجوال</span><span data-en>Phone</span></label>
                <div class="field--phone">
                  <span class="prefix">+966</span>
                  <input type="tel" name="phone" inputmode="numeric" pattern="[0-9]{8,10}" required>
                </div>
              </div>
            </div>

            <div class="field">
              <label data-i18n><span data-ar>البريد الإلكتروني (اختياري)</span><span data-en>Email (optional)</span></label>
              <input type="email" name="email">
            </div>

            <div class="field">
              <label data-i18n><span data-ar>رسالتك</span><span data-en>Your message</span></label>
              <textarea rows="4" name="message" required></textarea>
            </div>

            <button type="submit" class="btn btn--primary form__submit" data-i18n>
              <span data-ar>إرسال</span><span data-en>Send message</span>
              {!! $arrow !!}
            </button>

            <p class="form__success" style="display:none; margin: 6px 0 0; color: var(--mint-ink); background: var(--mint); padding: 12px 14px; border-radius: 12px; font-weight: 600;" data-i18n="block">
              <span data-ar>تم استلام رسالتك. سنتواصل معك قريبًا.</span>
              <span data-en>Got it — we'll be in touch shortly.</span>
            </p>
          </div>
        </form>
      </div>
    </div>
  </section>

  <!-- CTA BAND -->
  <section class="band">
    <div class="container band__inner">
      <div data-reveal>
        <h2 class="band__title" data-i18n="block">
          <span data-ar>راحتك وجودة خدمتك أولويتنا.</span>
          <span data-en>Your comfort and quality, first.</span>
        </h2>
        <p class="band__sub" data-i18n="block">
          <span data-ar>تواصل معنا في أي وقت وسيرد عليك فريقنا خلال دقائق — كل أيام الأسبوع.</span>
          <span data-en>Reach out anytime — our team replies in minutes, every day of the week.</span>
        </p>
      </div>
      <a href="#contact" class="band__cta" data-i18n>
        <span data-ar>تواصل معنا</span><span data-en>Contact us</span>
        {!! $arrow !!}
      </a>
    </div>
  </section>

</main>

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
        <h4 class="footer__h" data-i18n><span data-ar>تواصل</span><span data-en>Contact</span></h4>
        <ul class="footer__list">
          <li class="tnum"><a href="tel:+966559809687" dir="ltr" style="display:inline-block;">+966 55 980 9687</a></li>
          <li><a href="mailto:info@velto.sa">info@velto.sa</a></li>
          <li><a href="https://velto.sa">velto.sa</a></li>
        </ul>
      </div>
      <div>
        <h4 class="footer__h" data-i18n><span data-ar>تابعنا</span><span data-en>Follow</span></h4>
        <ul class="footer__list">
          <li><a href="https://instagram.com/Veltoapp">Instagram · @Veltoapp</a></li>
          <li><a href="https://tiktok.com/@Veltoapp">TikTok · @Veltoapp</a></li>
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

<script>
  // -------- Language toggle (AR/EN) --------
  (function () {
    var html = document.documentElement;
    var stored = null;
    try { stored = localStorage.getItem('velto.lang'); } catch (e) {}
    if (stored === 'en' || stored === 'ar') applyLang(stored);

    document.querySelectorAll('[data-set-lang]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        applyLang(btn.getAttribute('data-set-lang'));
      });
    });

    function applyLang(lang) {
      html.setAttribute('lang', lang);
      html.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');
      html.setAttribute('data-lang', lang);
      document.querySelectorAll('[data-set-lang]').forEach(function (b) {
        b.classList.toggle('is-active', b.getAttribute('data-set-lang') === lang);
      });
      try { localStorage.setItem('velto.lang', lang); } catch (e) {}
    }
  })();

  // -------- Sticky nav border --------
  (function () {
    var nav = document.getElementById('nav');
    var onScroll = function () {
      if (window.scrollY > 4) nav.classList.add('is-scrolled');
      else nav.classList.remove('is-scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  })();

  // -------- Reveal-on-scroll --------
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add('is-in');
          io.unobserve(e.target);
        }
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });
    document.querySelectorAll('[data-reveal]').forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll('[data-reveal]').forEach(function (el) { el.classList.add('is-in'); });
  }

  // -------- FAQ accordion --------
  document.querySelectorAll('#faqList .faq__q').forEach(function (q) {
    q.addEventListener('click', function () {
      var item = q.closest('.faq__item');
      var open = item.classList.contains('is-open');
      document.querySelectorAll('#faqList .faq__item').forEach(function (i) { i.classList.remove('is-open'); });
      if (!open) item.classList.add('is-open');
    });
  });

  // -------- Smooth scroll --------
  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href^="#"]');
    if (!a) return;
    var id = a.getAttribute('href');
    if (id.length < 2) return;
    var target = document.querySelector(id);
    if (!target) return;
    e.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
</script>
</body>
</html>
