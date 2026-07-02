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

  @font-face { font-family: 'Cairo'; src: url('/fonts/cairo/Cairo-Variable.ttf') format('truetype-variations'); font-weight: 200 1000; font-style: normal; font-display: swap; }

  /* ---------- Tokens ---------- */
  :root {
    --purple:    #8863E5;
    --purple-2:  #7a55da;
    --purple-3:  #6a45cf;
    --lavender:  #B38BEE;
    --lilac:     #CBB5F3;
    --mint:      #C9E3DA;
    --mint-2:    #A9D4C6;
    --mint-ink:  #245241;
    --bg:        #FAFAFB;
    --surface:   #FFFFFF;
    --fg:        #171127;
    --muted:     #6B6580;
    --border:    #EDEAF6;
    --ink:       #0E0820;

    --grad-hero:  radial-gradient(120% 120% at 15% 0%, #9a78ef 0%, #8863E5 42%, #7350d8 100%);
    --grad-brand: linear-gradient(135deg, #8863E5 0%, #B38BEE 100%);
    --glow:       0 30px 70px -30px rgba(136,99,229,0.55);
    --shadow-sm:  0 10px 30px -18px rgba(23,17,39,0.28);
    --shadow-lg:  0 40px 80px -46px rgba(23,17,39,0.4);

    --max:  1200px;
    --pad:  clamp(20px, 4vw, 40px);
    --r:    26px;
    --r-lg: 34px;

    --font-latin:  'Poppins', system-ui, sans-serif;
    --font-arabic: 'Cairo', 'Poppins', system-ui, sans-serif;
  }

  /* ---------- Reset ---------- */
  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }
  html { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility; scroll-behavior: smooth; }
  body {
    background: var(--bg);
    color: var(--fg);
    font-size: 16px;
    line-height: 1.75;
    overflow-x: hidden;
    font-family: var(--font-arabic);
  }
  html[data-lang="en"] body { font-family: var(--font-latin); line-height: 1.6; }
  img, svg { display: block; max-width: 100%; }
  a { color: inherit; text-decoration: none; }
  button { font: inherit; cursor: pointer; border: 0; background: transparent; color: inherit; }
  ::selection { background: var(--purple); color: #fff; }
  .tnum { font-variant-numeric: tabular-nums; }

  /* Per-language visibility */
  [data-i18n] [data-en], [data-i18n] [data-ar] { display: none; }
  html[data-lang="ar"] [data-i18n] [data-ar] { display: inline; }
  html[data-lang="en"] [data-i18n] [data-en] { display: inline; }
  [data-i18n="block"] [data-en], [data-i18n="block"] [data-ar] { display: none; }
  html[data-lang="ar"] [data-i18n="block"] [data-ar] { display: block; }
  html[data-lang="en"] [data-i18n="block"] [data-en] { display: block; }

  /* ---------- Layout primitives ---------- */
  .container { max-width: var(--max); margin: 0 auto; padding: 0 var(--pad); }
  section { padding: clamp(64px, 9vw, 120px) 0; position: relative; }

  .eyebrow {
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.16em; text-transform: uppercase;
    display: inline-flex; align-items: center; gap: 10px;
    color: var(--purple);
  }
  html[data-lang="ar"] .eyebrow { letter-spacing: 0.03em; }
  .eyebrow--pill {
    background: rgba(136,99,229,0.09); border: 1px solid rgba(136,99,229,0.16);
    padding: 7px 14px; border-radius: 999px;
  }
  .eyebrow--onpurple { color: #fff; background: rgba(255,255,255,0.14); border-color: rgba(255,255,255,0.22); }

  .section__head { max-width: 640px; margin: 0 auto clamp(40px, 6vw, 64px); text-align: center; }
  .section__head--start { margin-inline: 0; text-align: start; }
  .section__title {
    font-weight: 800;
    font-size: clamp(30px, 4.2vw, 50px);
    line-height: 1.16; letter-spacing: -0.02em;
    margin: 16px 0 0;
  }
  html[data-lang="en"] .section__title { font-style: italic; line-height: 1.05; letter-spacing: -0.03em; }
  .section__title .hl { color: var(--purple); }
  .section__sub { color: var(--muted); font-size: clamp(15px, 1.4vw, 18px); margin: 14px 0 0; }

  /* ---------- Logo + wordmark ---------- */
  .logo-img { height: 32px; width: auto; transition: filter .25s ease; }
  .word {
    font-family: var(--font-latin);
    font-weight: 800; font-style: italic;
    letter-spacing: -0.02em;
    display: inline-flex; align-items: center; gap: 0.05em; line-height: 1;
  }
  .word .spark { width: 0.42em; height: 0.42em; margin-bottom: 0.55em; color: currentColor; flex: none; }

  /* ---------- Buttons ---------- */
  .btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 15px 26px; border-radius: 999px;
    font-weight: 700; font-size: 15px; white-space: nowrap;
    border: 1px solid transparent;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
  }
  .btn--primary { background: var(--purple); color: #fff; box-shadow: var(--glow); }
  .btn--primary:hover { transform: translateY(-2px); background: var(--purple-2); }
  .btn--white { background: #fff; color: var(--purple); box-shadow: 0 20px 40px -24px rgba(0,0,0,0.5); }
  .btn--white:hover { transform: translateY(-2px); }
  .btn--ghost { background: transparent; color: var(--fg); border-color: rgba(23,17,39,0.16); }
  .btn--ghost:hover { border-color: var(--fg); }
  .btn--glass { background: rgba(255,255,255,0.14); color: #fff; border-color: rgba(255,255,255,0.35); backdrop-filter: blur(6px); }
  .btn--glass:hover { background: rgba(255,255,255,0.24); transform: translateY(-2px); }

  .arrow { width: 16px; height: 16px; flex: none; }
  html[dir="rtl"] .arrow { transform: scaleX(-1); }

  /* ---------- Nav ---------- */
  .nav {
    position: fixed; inset: 0 0 auto 0; z-index: 60;
    transition: background .25s ease, box-shadow .25s ease, border-color .25s ease;
    border-bottom: 1px solid transparent;
  }
  .nav__inner { display: flex; align-items: center; justify-content: space-between; height: 74px; gap: 16px; }
  .nav__brand { display: inline-flex; align-items: center; }
  .nav__links { display: none; gap: 30px; font-size: 15px; }
  .nav__links a { color: #fff; opacity: 0.85; font-weight: 500; transition: opacity .15s ease; }
  .nav__links a:hover { opacity: 1; }
  .nav__right { display: inline-flex; align-items: center; gap: 10px; }

  .lang-toggle {
    display: inline-flex; align-items: center;
    border: 1px solid rgba(255,255,255,0.4); border-radius: 999px;
    padding: 3px; font-family: var(--font-latin);
    transition: border-color .25s ease;
  }
  .lang-toggle button {
    padding: 6px 11px; border-radius: 999px; font-size: 12px; font-weight: 700;
    color: rgba(255,255,255,0.8); letter-spacing: .04em;
    transition: background .15s ease, color .15s ease;
  }
  .lang-toggle button.is-active { background: #fff; color: var(--purple); }

  .nav__cta { display: none; }
  .nav__menu { display: inline-flex; padding: 8px; }
  .nav__menu svg { width: 24px; height: 24px; color: #fff; }

  /* Scrolled state → light frosted nav */
  .nav.is-scrolled {
    background: rgba(255,255,255,0.82);
    -webkit-backdrop-filter: saturate(150%) blur(14px);
    backdrop-filter: saturate(150%) blur(14px);
    border-bottom-color: var(--border);
    box-shadow: 0 8px 30px -22px rgba(23,17,39,0.4);
  }
  .nav.is-scrolled .logo-img { filter: none; }
  .nav.is-scrolled .nav__links a { color: var(--fg); }
  .nav.is-scrolled .nav__links a:hover { color: var(--purple); opacity: 1; }
  .nav.is-scrolled .lang-toggle { border-color: var(--border); }
  .nav.is-scrolled .lang-toggle button { color: var(--muted); }
  .nav.is-scrolled .lang-toggle button.is-active { background: var(--fg); color: #fff; }
  .nav.is-scrolled .nav__menu svg { color: var(--fg); }
  /* logo is white over the hero */
  .nav:not(.is-scrolled) .logo-img { filter: brightness(0) invert(1); }

  @media (min-width: 1000px) {
    .nav__links { display: inline-flex; }
    .nav__menu { display: none; }
    .nav__cta { display: inline-flex; }
    .nav.is-scrolled .nav__cta { background: var(--purple); color: #fff; border-color: transparent; }
  }

  /* Mobile drawer */
  .drawer {
    position: fixed; inset: 0; z-index: 70;
    background: rgba(14,8,32,0.5); backdrop-filter: blur(4px);
    opacity: 0; visibility: hidden; transition: opacity .25s ease, visibility .25s ease;
  }
  .drawer.is-open { opacity: 1; visibility: visible; }
  .drawer__panel {
    position: absolute; inset-inline-end: 0; top: 0; bottom: 0;
    width: min(84vw, 340px); background: #fff;
    padding: 24px 24px 32px; display: flex; flex-direction: column; gap: 8px;
    transform: translateX(0); transition: transform .3s cubic-bezier(.2,.7,.2,1);
    box-shadow: -30px 0 60px -30px rgba(0,0,0,0.4);
  }
  html[dir="rtl"] .drawer__panel { transform: translateX(0); }
  .drawer:not(.is-open) .drawer__panel { transform: translateX(100%); }
  html[dir="rtl"] .drawer:not(.is-open) .drawer__panel { transform: translateX(-100%); }
  .drawer__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
  .drawer__head .logo-img { filter: none; }
  .drawer__close { padding: 6px; }
  .drawer__close svg { width: 24px; height: 24px; color: var(--fg); }
  .drawer a.drawer__link { padding: 14px 4px; font-size: 18px; font-weight: 600; border-bottom: 1px solid var(--border); }
  .drawer .btn { margin-top: 16px; justify-content: center; }

  /* ---------- HERO ---------- */
  .hero {
    position: relative; isolation: isolate; overflow: hidden;
    background: var(--grad-hero);
    color: #fff;
    padding: clamp(120px, 18vw, 190px) 0 clamp(90px, 12vw, 150px);
  }
  .hero__pattern {
    position: absolute; inset: 0; z-index: -2;
    background: url('/img/pattern.png') center/540px repeat;
    opacity: 0.10; mix-blend-mode: soft-light; pointer-events: none;
  }
  .hero__blob {
    position: absolute; border-radius: 50%; filter: blur(60px);
    z-index: -1; pointer-events: none; opacity: 0.55;
  }
  .hero__blob--1 { width: 480px; height: 480px; background: #C9E3DA; top: -120px; inset-inline-end: -80px; opacity: 0.35; }
  .hero__blob--2 { width: 520px; height: 520px; background: #CBB5F3; bottom: -220px; inset-inline-start: -120px; opacity: 0.45; }
  .hero__wave { position: absolute; left: 0; right: 0; bottom: -1px; z-index: 0; pointer-events: none; }
  .hero__wave svg { width: 100%; height: auto; display: block; }

  .hero__grid { display: grid; grid-template-columns: 1fr; gap: clamp(40px, 6vw, 64px); align-items: center; position: relative; z-index: 1; }
  @media (min-width: 980px) { .hero__grid { grid-template-columns: 1.1fr 0.9fr; } }

  .hero__head {
    font-weight: 800; font-size: clamp(40px, 7vw, 84px);
    line-height: 1.08; letter-spacing: -0.03em; margin: 22px 0 0;
  }
  html[data-lang="ar"] .hero__head { line-height: 1.24; letter-spacing: 0; }
  html[data-lang="en"] .hero__head { font-style: italic; }
  .hero__head .u { position: relative; white-space: nowrap; }
  html[data-lang="ar"] .hero__head .u { white-space: normal; }
  .hero__head .u::after {
    content: ""; position: absolute; left: -2%; right: -2%; bottom: 0.06em;
    height: 0.30em; background: var(--mint); z-index: -1; border-radius: 4px;
  }
  .hero__sub { font-size: clamp(16px, 1.5vw, 20px); color: rgba(255,255,255,0.9); max-width: 52ch; margin: 24px 0 34px; }
  .hero__ctas { display: flex; flex-wrap: wrap; gap: 12px; }
  .hero__meta { display: flex; flex-wrap: wrap; gap: 22px; margin-top: 30px; }
  .hero__meta-item { display: inline-flex; align-items: center; gap: 9px; font-size: 14px; color: rgba(255,255,255,0.92); font-weight: 600; }
  .hero__meta-item svg { width: 18px; height: 18px; color: var(--mint); flex: none; }

  /* Hero showcase card (phone-ish booking preview) */
  .showcase {
    position: relative;
    background: rgba(255,255,255,0.10);
    border: 1px solid rgba(255,255,255,0.28);
    border-radius: var(--r-lg);
    padding: 20px;
    backdrop-filter: blur(10px);
    box-shadow: var(--shadow-lg);
  }
  .showcase__inner { background: #fff; border-radius: 24px; padding: 22px; color: var(--fg); }
  .showcase__tag {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--mint); color: var(--mint-ink);
    padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700;
  }
  .showcase__tag .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--mint-ink); box-shadow: 0 0 0 3px rgba(36,82,65,0.2); }
  .showcase__title { margin: 16px 0 4px; font-weight: 800; font-size: 20px; }
  html[data-lang="en"] .showcase__title { font-style: italic; }
  .showcase__note { color: var(--muted); font-size: 13px; margin: 0 0 16px; }
  .slot {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 15px; border: 1px solid var(--border); border-radius: 16px; margin-bottom: 10px;
    transition: border-color .15s ease, background .15s ease;
  }
  .slot:hover { border-color: var(--lilac); }
  .slot.is-active { background: rgba(136,99,229,0.06); border-color: var(--purple); }
  .slot__l strong { font-weight: 700; font-size: 15px; }
  .slot__l small { display: block; color: var(--muted); font-size: 12px; }
  .slot__pill { background: var(--bg); color: var(--purple); padding: 4px 11px; border-radius: 999px; font-family: var(--font-latin); font-size: 12px; font-weight: 700; }
  .slot.is-active .slot__pill { background: var(--purple); color: #fff; }
  .showcase__cta { margin-top: 6px; width: 100%; justify-content: center; }
  .showcase__float {
    position: absolute; inset-inline-start: -16px; bottom: 26px;
    background: #fff; color: var(--fg); border-radius: 16px; padding: 12px 15px;
    box-shadow: var(--shadow-lg); display: flex; align-items: center; gap: 10px;
    font-weight: 700; font-size: 14px;
  }
  .showcase__float .star { width: 34px; height: 34px; border-radius: 11px; background: var(--grad-brand); color: #fff; display: grid; place-items: center; }
  .showcase__float .star svg { width: 18px; height: 18px; }
  .showcase__float small { display: block; color: var(--muted); font-weight: 500; font-size: 11px; }
  @media (max-width: 520px) { .showcase__float { display: none; } }

  /* ---------- Trust strip ---------- */
  .trust { background: var(--surface); border-bottom: 1px solid var(--border); }
  .trust__grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; padding: 34px 0; }
  @media (min-width: 760px) { .trust__grid { grid-template-columns: repeat(4, 1fr); } }
  .trust__cell { text-align: center; padding: 12px; }
  .trust__num { font-family: var(--font-latin); font-weight: 800; font-style: italic; font-size: clamp(30px, 4vw, 44px); line-height: 1; background: var(--grad-brand); -webkit-background-clip: text; background-clip: text; color: transparent; }
  .trust__lbl { color: var(--muted); font-size: 14px; margin-top: 8px; font-weight: 600; }

  /* ---------- How it works ---------- */
  .steps { display: grid; grid-template-columns: 1fr; gap: 20px; }
  @media (min-width: 820px) { .steps { grid-template-columns: repeat(3, 1fr); } }
  .step {
    position: relative; background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 32px 28px; overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .step:hover { transform: translateY(-4px); box-shadow: var(--shadow-sm); }
  .step__n {
    font-family: var(--font-latin); font-weight: 800; font-style: italic; font-size: 15px;
    width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center;
    background: var(--grad-brand); color: #fff; box-shadow: var(--glow);
  }
  .step__t { font-weight: 800; font-size: 21px; margin: 20px 0 8px; }
  html[data-lang="en"] .step__t { font-style: italic; }
  .step__d { color: var(--muted); font-size: 15px; margin: 0; }
  .step__ghost {
    position: absolute; inset-inline-end: 18px; top: 10px;
    font-family: var(--font-latin); font-weight: 800; font-style: italic;
    font-size: 92px; line-height: 1; color: var(--purple); opacity: 0.06; user-select: none;
  }

  /* ---------- Services bento ---------- */
  .bento { display: grid; grid-template-columns: 1fr; gap: 16px; }
  @media (min-width: 760px) { .bento { grid-template-columns: repeat(6, 1fr); grid-auto-rows: 1fr; } }
  .tile {
    position: relative; overflow: hidden;
    border-radius: var(--r-lg); padding: clamp(24px, 3vw, 36px);
    display: flex; flex-direction: column; min-height: 240px;
    transition: transform .25s ease, box-shadow .25s ease;
    border: 1px solid var(--border); background: var(--surface);
  }
  .tile:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
  @media (min-width: 760px) {
    .tile--wide { grid-column: span 4; }
    .tile--half { grid-column: span 3; }
    .tile--third { grid-column: span 2; }
    .tile--tall { grid-row: span 2; }
  }
  .tile--purple { background: var(--grad-hero); color: #fff; border-color: transparent; }
  .tile--mint { background: linear-gradient(150deg, #DcefE7, #C9E3DA); color: var(--mint-ink); border-color: transparent; }
  .tile--lav { background: linear-gradient(150deg, #E4D7F8, #CBB5F3); color: var(--ink); border-color: transparent; }
  .tile--ink { background: var(--ink); color: #fff; border-color: transparent; }
  .tile__pattern { position: absolute; inset: 0; background: url('/img/pattern.png') center/360px repeat; opacity: 0.09; mix-blend-mode: soft-light; pointer-events: none; }
  .tile--ink .tile__pattern, .tile--purple .tile__pattern { mix-blend-mode: screen; opacity: 0.08; }
  .tile__ico {
    width: 54px; height: 54px; border-radius: 16px; display: grid; place-items: center;
    background: rgba(136,99,229,0.10); color: var(--purple); margin-bottom: 18px;
  }
  .tile__ico svg { width: 26px; height: 26px; }
  .tile--purple .tile__ico, .tile--ink .tile__ico { background: rgba(255,255,255,0.16); color: #fff; }
  .tile--mint .tile__ico { background: rgba(36,82,65,0.12); color: var(--mint-ink); }
  .tile--lav .tile__ico { background: rgba(23,17,39,0.10); color: var(--purple-3); }
  .tile__k { position: relative; font-family: var(--font-latin); font-style: italic; font-weight: 700; font-size: 13px; opacity: .75; }
  .tile__t { position: relative; font-weight: 800; font-size: clamp(22px, 2.4vw, 30px); line-height: 1.2; margin: 6px 0 10px; letter-spacing: -0.01em; }
  html[data-lang="en"] .tile__t { font-style: italic; }
  .tile__d { position: relative; font-size: 15px; opacity: 0.9; margin: 0; }
  .tile--purple .tile__d, .tile--ink .tile__d { opacity: 0.82; }
  .tile__link {
    position: relative; margin-top: auto; padding-top: 18px;
    display: inline-flex; align-items: center; gap: 8px; font-weight: 700;
  }
  .tile__link .arrow { transition: transform .2s ease; }
  .tile:hover .tile__link .arrow { transform: translateX(4px); }
  html[dir="rtl"] .tile:hover .tile__link .arrow { transform: translateX(-4px) scaleX(-1); }
  .tile__big { position: relative; font-weight: 800; font-size: clamp(38px, 5vw, 58px); line-height: 1; letter-spacing: -0.02em; margin: 4px 0; }
  html[data-lang="en"] .tile__big { font-style: italic; }

  /* ---------- Why (feature cards) ---------- */
  .why { background: linear-gradient(180deg, #fff, var(--bg)); border-top: 1px solid var(--border); }
  .why-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
  @media (min-width: 640px) { .why-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (min-width: 980px) { .why-grid { grid-template-columns: repeat(3, 1fr); } }
  .feat { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 28px; transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease; }
  .feat:hover { transform: translateY(-3px); border-color: var(--lilac); box-shadow: var(--shadow-sm); }
  .feat__ico { width: 50px; height: 50px; border-radius: 14px; background: rgba(136,99,229,0.09); color: var(--purple); display: grid; place-items: center; margin-bottom: 16px; }
  .feat__ico svg { width: 24px; height: 24px; }
  .feat__t { font-weight: 800; font-size: 18px; margin: 0 0 6px; }
  .feat__d { color: var(--muted); font-size: 14px; margin: 0; }

  /* ---------- Packages ---------- */
  .plans { display: grid; grid-template-columns: 1fr; gap: 18px; align-items: stretch; }
  @media (min-width: 900px) { .plans { grid-template-columns: repeat(3, 1fr); } }
  .plan {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg);
    padding: clamp(26px, 3vw, 36px); display: flex; flex-direction: column;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .plan:hover { transform: translateY(-4px); box-shadow: var(--shadow-sm); }
  .plan--featured {
    background: var(--grad-hero); color: #fff; border-color: transparent;
    box-shadow: var(--glow); position: relative; overflow: hidden;
  }
  .plan--featured .plan__pattern { position: absolute; inset: 0; background: url('/img/pattern.png') center/340px repeat; opacity: 0.09; mix-blend-mode: screen; }
  .plan__badge { position: relative; align-self: flex-start; background: var(--mint); color: var(--mint-ink); font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 999px; margin-bottom: 14px; }
  .plan__name { position: relative; font-weight: 800; font-size: 22px; margin: 0 0 6px; }
  html[data-lang="en"] .plan__name { font-style: italic; }
  .plan__for { position: relative; color: var(--muted); font-size: 14px; margin: 0 0 20px; }
  .plan--featured .plan__for { color: rgba(255,255,255,0.85); }
  .plan__list { position: relative; list-style: none; margin: 0 0 24px; padding: 0; display: grid; gap: 12px; flex: 1; }
  .plan__list li { display: flex; align-items: flex-start; gap: 10px; font-size: 15px; }
  .plan__list li svg { width: 20px; height: 20px; color: var(--purple); flex: none; margin-top: 2px; }
  .plan--featured .plan__list li svg { color: var(--mint); }
  .plan .btn { position: relative; width: 100%; justify-content: center; }

  /* ---------- App download band ---------- */
  .app { background: var(--ink); color: #fff; overflow: hidden; }
  .app__inner { display: grid; grid-template-columns: 1fr; gap: 40px; align-items: center; }
  @media (min-width: 900px) { .app__inner { grid-template-columns: 1.1fr 0.9fr; } }
  .app__title { font-weight: 800; font-size: clamp(30px, 4.4vw, 52px); line-height: 1.14; letter-spacing: -0.02em; margin: 16px 0 12px; }
  html[data-lang="en"] .app__title { font-style: italic; line-height: 1.02; }
  .app__sub { color: rgba(255,255,255,0.75); font-size: 17px; max-width: 46ch; margin: 0 0 28px; }
  .store { display: flex; flex-wrap: wrap; gap: 12px; }
  .store a {
    display: inline-flex; align-items: center; gap: 12px;
    background: #fff; color: var(--ink); border-radius: 16px; padding: 12px 20px 12px 16px;
    transition: transform .18s ease;
  }
  html[dir="rtl"] .store a { padding: 12px 16px 12px 20px; }
  .store a:hover { transform: translateY(-2px); }
  .store a svg { width: 26px; height: 26px; flex: none; }
  .store a small { display: block; font-size: 11px; opacity: 0.7; line-height: 1; }
  .store a strong { display: block; font-family: var(--font-latin); font-weight: 700; font-size: 16px; line-height: 1.25; }
  .app__art { position: relative; display: grid; place-items: center; min-height: 220px; }
  .app__phone {
    width: min(260px, 70%); aspect-ratio: 9/17; border-radius: 34px;
    background: var(--grad-hero); border: 8px solid rgba(255,255,255,0.12);
    box-shadow: var(--shadow-lg); position: relative; overflow: hidden;
  }
  .app__phone::before { content: ""; position: absolute; inset: 0; background: url('/img/pattern.png') center/240px repeat; opacity: 0.12; mix-blend-mode: soft-light; }
  .app__phone .word { position: absolute; inset: 0; margin: auto; height: fit-content; width: fit-content; color: #fff; font-size: 34px; }
  .app__notch { position: absolute; top: 14px; left: 50%; transform: translateX(-50%); width: 46%; height: 20px; background: rgba(0,0,0,0.35); border-radius: 999px; }

  /* ---------- FAQ ---------- */
  .faq { max-width: 820px; margin: 0 auto; }
  .faq__item { border: 1px solid var(--border); border-radius: 18px; margin-bottom: 12px; background: var(--surface); overflow: hidden; transition: border-color .2s ease, box-shadow .2s ease; }
  .faq__item.is-open { border-color: var(--lilac); box-shadow: var(--shadow-sm); }
  .faq__q { width: 100%; display: grid; grid-template-columns: auto 1fr auto; gap: 16px; align-items: center; padding: 20px 22px; text-align: start; }
  .faq__num { font-family: var(--font-latin); font-weight: 800; font-style: italic; color: var(--lavender); font-size: 15px; min-width: 28px; }
  .faq__q-text { font-weight: 700; font-size: clamp(16px, 1.9vw, 19px); line-height: 1.4; }
  html[data-lang="en"] .faq__q-text { font-style: italic; }
  .faq__icon { width: 34px; height: 34px; border-radius: 999px; border: 1px solid var(--border); display: grid; place-items: center; transition: transform .25s ease, background .25s ease, border-color .25s ease, color .25s ease; color: var(--fg); flex: none; }
  .faq__icon svg { width: 14px; height: 14px; transition: transform .25s ease; }
  .faq__item.is-open .faq__icon { background: var(--purple); border-color: var(--purple); color: #fff; }
  .faq__item.is-open .faq__icon svg { transform: rotate(45deg); }
  .faq__a { max-height: 0; overflow: hidden; transition: max-height .35s ease; }
  .faq__a-inner { padding: 0 22px 22px; color: var(--muted); max-width: 62ch; }
  html[dir="rtl"] .faq__q { padding-inline: 22px; }
  .faq__item.is-open .faq__a { max-height: 340px; }

  /* ---------- Contact ---------- */
  .contact-grid { display: grid; grid-template-columns: 1fr; gap: 22px; }
  @media (min-width: 980px) { .contact-grid { grid-template-columns: 0.9fr 1.1fr; gap: 32px; } }
  .contact-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); padding: clamp(26px, 3vw, 40px); }
  .contact-card--info { background: var(--grad-hero); color: #fff; border-color: transparent; position: relative; overflow: hidden; }
  .contact-card--info .cc__pattern { position: absolute; inset: 0; background: url('/img/pattern.png') center/360px repeat; opacity: 0.09; mix-blend-mode: screen; }
  .contact-card h3 { position: relative; margin: 0 0 8px; font-weight: 800; font-size: 24px; }
  html[data-lang="en"] .contact-card h3 { font-style: italic; }
  .contact-card--info p { position: relative; color: rgba(255,255,255,0.85); margin: 0; }
  .contact-list { position: relative; list-style: none; padding: 0; margin: 24px 0 0; display: grid; gap: 16px; }
  .contact-list li { display: flex; align-items: center; gap: 14px; }
  .contact-list .ico { width: 44px; height: 44px; border-radius: 13px; background: rgba(255,255,255,0.16); color: #fff; display: grid; place-items: center; flex: none; }
  .contact-list .ico svg { width: 19px; height: 19px; }
  .contact-list a, .contact-list span.v { color: #fff; font-weight: 700; }
  .contact-list small { display: block; color: rgba(255,255,255,0.7); font-weight: 500; font-size: 12px; }
  .contact-list .tnum a { font-family: var(--font-latin); font-variant-numeric: tabular-nums; direction: ltr; unicode-bidi: isolate; }

  .form { display: grid; gap: 15px; margin-top: 22px; }
  .form__row { display: grid; gap: 15px; grid-template-columns: 1fr; }
  @media (min-width: 640px) { .form__row { grid-template-columns: 1fr 1fr; } }
  .field { display: grid; gap: 7px; }
  .field label { font-size: 12px; font-weight: 700; color: var(--muted); letter-spacing: 0.05em; text-transform: uppercase; }
  .field input, .field textarea { background: var(--bg); border: 1px solid var(--border); border-radius: 14px; padding: 14px 16px; color: var(--fg); font: inherit; transition: border-color .15s ease, box-shadow .15s ease, background .15s ease; }
  .field input:focus, .field textarea:focus { outline: none; border-color: var(--purple); background: #fff; box-shadow: 0 0 0 4px rgba(136,99,229,0.12); }
  .field--phone { display: grid; grid-template-columns: auto 1fr; gap: 10px; }
  .field--phone .prefix { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 14px; font-family: var(--font-latin); font-weight: 700; direction: ltr; unicode-bidi: isolate; display: inline-flex; align-items: center; }
  .form__submit { justify-self: start; }

  /* ---------- Footer ---------- */
  footer { background: var(--ink); color: rgba(255,255,255,0.7); padding: 72px 0 36px; font-size: 14px; }
  .footer__top { display: grid; grid-template-columns: 1fr; gap: 40px; padding-bottom: 44px; border-bottom: 1px solid rgba(255,255,255,0.08); }
  @media (min-width: 880px) { .footer__top { grid-template-columns: 2fr 1fr 1fr 1fr; } }
  .footer__brand img { height: 34px; filter: brightness(0) invert(1); }
  .footer__tagline { margin-top: 18px; max-width: 34ch; line-height: 1.7; }
  .footer__h { color: #fff; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.14em; margin: 0 0 14px; }
  .footer__list { list-style: none; padding: 0; margin: 0; display: grid; gap: 9px; }
  .footer__list a { transition: color .15s ease; }
  .footer__list a:hover { color: #fff; }
  .footer__bottom { display: flex; flex-wrap: wrap; gap: 18px; justify-content: space-between; padding-top: 26px; color: rgba(255,255,255,0.45); font-size: 12px; }

  /* ---------- Reveal on scroll ---------- */
  @media (prefers-reduced-motion: no-preference) {
    [data-reveal] { opacity: 0; transform: translateY(16px); transition: opacity .7s cubic-bezier(.2,.7,.2,1), transform .7s cubic-bezier(.2,.7,.2,1); }
    [data-reveal].is-in { opacity: 1; transform: none; }
    [data-reveal][data-delay="1"] { transition-delay: .08s; }
    [data-reveal][data-delay="2"] { transition-delay: .16s; }
    [data-reveal][data-delay="3"] { transition-delay: .24s; }
  }
</style>
</head>
<body>

@php
  $spark = '<svg class="spark" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c.6 6 4.4 9.8 12 12-7.6 2.2-11.4 6-12 12-.6-6-4.4-9.8-12-12C7.6 9.8 11.4 6 12 0z" fill="currentColor"/></svg>';
  $arrow = '<svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>';
  $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>';
@endphp

<!-- NAV -->
<header class="nav" id="nav">
  <div class="container nav__inner">
    <a href="#top" class="nav__brand" aria-label="Velto">
      <img src="/img/logo-velto.png" alt="Velto" class="logo-img">
    </a>

    <nav class="nav__links" aria-label="Primary">
      <a href="#how" data-i18n><span data-ar>كيف نعمل</span><span data-en>How it works</span></a>
      <a href="#services" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></a>
      <a href="#plans" data-i18n><span data-ar>الباقات</span><span data-en>Packages</span></a>
      <a href="#faq" data-i18n><span data-ar>الأسئلة</span><span data-en>FAQ</span></a>
      <a href="#contact" data-i18n><span data-ar>تواصل</span><span data-en>Contact</span></a>
    </nav>

    <div class="nav__right">
      <div class="lang-toggle" role="group" aria-label="Language">
        <button type="button" data-set-lang="ar" class="is-active">AR</button>
        <button type="button" data-set-lang="en">EN</button>
      </div>
      <a href="#contact" class="btn btn--white nav__cta" data-i18n>
        <span data-ar>احجز الآن</span><span data-en>Book now</span>
        {!! $arrow !!}
      </a>
      <button class="nav__menu" id="menuBtn" aria-label="Menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
    </div>
  </div>
</header>

<!-- MOBILE DRAWER -->
<div class="drawer" id="drawer">
  <div class="drawer__panel">
    <div class="drawer__head">
      <img src="/img/logo-velto.png" alt="Velto" class="logo-img">
      <button class="drawer__close" id="drawerClose" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <a class="drawer__link" href="#how" data-i18n><span data-ar>كيف نعمل</span><span data-en>How it works</span></a>
    <a class="drawer__link" href="#services" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></a>
    <a class="drawer__link" href="#plans" data-i18n><span data-ar>الباقات</span><span data-en>Packages</span></a>
    <a class="drawer__link" href="#faq" data-i18n><span data-ar>الأسئلة الشائعة</span><span data-en>FAQ</span></a>
    <a class="drawer__link" href="#contact" data-i18n><span data-ar>تواصل معنا</span><span data-en>Contact</span></a>
    <a href="#contact" class="btn btn--primary" data-i18n><span data-ar>احجز الآن</span><span data-en>Book now</span>{!! $arrow !!}</a>
  </div>
</div>

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
          <span data-ar>فريق Velto المدرّب يصلك أينما كنت — في بيتك، في عملك، في يومك. بدون مواعيد طويلة، وبدون تعقيد. جودة تفوق توقعاتك.</span>
          <span data-en>A trained Velto detailer comes to your driveway, your office, your everyday. Same-day, no water mess, no waiting in lines.</span>
        </p>
        <div class="hero__ctas">
          <a href="#contact" class="btn btn--white" data-i18n>
            <span data-ar>احجز الآن</span><span data-en>Book now</span>
            {!! $arrow !!}
          </a>
          <a href="https://wa.me/966559809687" class="btn btn--glass" data-i18n>
            <span data-ar>واتساب</span><span data-en>WhatsApp</span>
          </a>
        </div>
        <div class="hero__meta">
          <span class="hero__meta-item" data-i18n>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <span data-ar>خدمة متنقلة ١٠٠٪</span><span data-en>100% mobile</span>
          </span>
          <span class="hero__meta-item" data-i18n>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <span data-ar>مواعيد نفس اليوم</span><span data-en>Same-day slots</span>
          </span>
          <span class="hero__meta-item" data-i18n>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <span data-ar>كل أيام الأسبوع</span><span data-en>Every day</span>
          </span>
        </div>
      </div>

      <aside class="showcase" data-reveal data-delay="1">
        <div class="showcase__inner">
          <span class="showcase__tag" data-i18n><span class="dot"></span><span data-ar>مواعيد متاحة اليوم</span><span data-en>Slots open today</span></span>
          <h2 class="showcase__title" data-i18n><span data-ar>اختر وقتك</span><span data-en>Pick your time</span></h2>
          <p class="showcase__note" data-i18n><span data-ar>نصلك خلال ساعة من الموعد المختار</span><span data-en>We arrive within the hour of your slot</span></p>

          <div class="slot is-active tnum">
            <div class="slot__l">
              <strong data-i18n><span data-ar>غسيل خارجي سريع</span><span data-en>Express exterior</span></strong>
              <small>4:30 PM · 45 min</small>
            </div>
            <span class="slot__pill" data-i18n><span data-ar>مختار</span><span data-en>Selected</span></span>
          </div>
          <div class="slot tnum">
            <div class="slot__l">
              <strong data-i18n><span data-ar>تنظيف متكامل</span><span data-en>Full detail</span></strong>
              <small>6:00 PM · 90 min</small>
            </div>
            <span class="slot__pill" data-i18n><span data-ar>متاح</span><span data-en>Open</span></span>
          </div>

          <a href="#contact" class="btn btn--primary showcase__cta" data-i18n>
            <span data-ar>تأكيد الحجز</span><span data-en>Confirm booking</span>
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
          <span class="step__ghost">01</span>
          <div class="step__n">01</div>
          <h3 class="step__t" data-i18n><span data-ar>احجز في دقيقة</span><span data-en>Book in a minute</span></h3>
          <p class="step__d" data-i18n><span data-ar>اختر الخدمة والوقت الذي يناسبك من التطبيق أو عبر واتساب. بدون مكالمات ولا انتظار.</span><span data-en>Choose your service and a time that suits you — in the app or on WhatsApp. No calls, no waiting.</span></p>
        </div>
        <div class="step" data-reveal data-delay="1">
          <span class="step__ghost">02</span>
          <div class="step__n">02</div>
          <h3 class="step__t" data-i18n><span data-ar>نصل إليك</span><span data-en>We come to you</span></h3>
          <p class="step__d" data-i18n><span data-ar>يصلك متخصص Velto بكامل أدواته ونظام الغسيل اللامائي. موقف عادي يكفي — لا ماء ولا تصريف.</span><span data-en>A Velto detailer arrives fully equipped with a waterless system. A regular parking spot is all it takes.</span></p>
        </div>
        <div class="step" data-reveal data-delay="2">
          <span class="step__ghost">03</span>
          <div class="step__n">03</div>
          <h3 class="step__t" data-i18n><span data-ar>استمتع باللمعان</span><span data-en>Enjoy the shine</span></h3>
          <p class="step__d" data-i18n><span data-ar>سيارة نظيفة ولامعة دون أن تغادر مكانك. قيّم الخدمة من التطبيق بعد كل زيارة.</span><span data-en>A spotless, gleaming car without leaving your spot. Rate the visit in the app when it's done.</span></p>
        </div>
      </div>
    </div>
  </section>

  <!-- SERVICES BENTO -->
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
        <!-- Big purple tile -->
        <article class="tile tile--purple tile--wide tile--tall" data-reveal>
          <div class="tile__pattern" aria-hidden="true"></div>
          <div class="tile__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13M5 13h14v4a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H8v1a1 1 0 01-1 1H6a1 1 0 01-1-1v-4z"/><circle cx="7.5" cy="15.5" r="1"/><circle cx="16.5" cy="15.5" r="1"/></svg>
          </div>
          <span class="tile__k">01</span>
          <h3 class="tile__t" data-i18n><span data-ar>غسيل خارجي سريع</span><span data-en>Express exterior wash</span></h3>
          <p class="tile__d" data-i18n="block">
            <span data-ar>تنظيف شامل لجسم السيارة الخارجي بمواد عالية الجودة تضمن لمعانًا فوريًا وإزالة الأتربة والأوساخ بسرعة وكفاءة.</span>
            <span data-en>A full exterior clean with premium products — instant gloss, dust and grime lifted quickly and efficiently.</span>
          </p>
          <a href="#contact" class="tile__link" data-i18n><span data-ar>احجز الآن</span><span data-en>Book now</span>{!! $arrow !!}</a>
        </article>

        <!-- Mint tile -->
        <article class="tile tile--mint tile--third" data-reveal data-delay="1">
          <div class="tile__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
          </div>
          <h3 class="tile__t" data-i18n><span data-ar>مواد آمنة</span><span data-en>Safe products</span></h3>
          <p class="tile__d" data-i18n><span data-ar>مُختبرة وآمنة تمامًا على طلاء سيارتك.</span><span data-en>Tested and fully safe on your paint.</span></p>
        </article>

        <!-- Lavender tile -->
        <article class="tile tile--lav tile--third" data-reveal data-delay="2">
          <div class="tile__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h13l3-3v6l-3-3M3 6h10M3 18h10"/></svg>
          </div>
          <h3 class="tile__t" data-i18n><span data-ar>نصل إليك</span><span data-en>We come to you</span></h3>
          <p class="tile__d" data-i18n><span data-ar>البيت، العمل، أينما كانت سيارتك.</span><span data-en>Home, office, wherever you park.</span></p>
        </article>

        <!-- Half: Full detail -->
        <article class="tile tile--half" data-reveal>
          <div class="tile__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 5 5.6.6-4 4 1 5.4L12 19l-5 3 1-5.4-4-4 5.6-.6z"/></svg>
          </div>
          <span class="tile__k">02</span>
          <h3 class="tile__t" data-i18n><span data-ar>تنظيف متكامل</span><span data-en>Full detail</span></h3>
          <p class="tile__d" data-i18n="block">
            <span data-ar>من الخارج والداخل بدقة — المقاعد والأرضيات والطبلون — مع اهتمام بالتفاصيل يعيد لكل سطح انتعاشه.</span>
            <span data-en>Inside and out — seats, floors, dashboard — with the detail that brings every surface back to life.</span>
          </p>
          <a href="#contact" class="tile__link" style="color: var(--purple);" data-i18n><span data-ar>احجز الآن</span><span data-en>Book now</span>{!! $arrow !!}</a>
        </article>

        <!-- Ink: rating/quality -->
        <article class="tile tile--ink tile--half" data-reveal data-delay="1">
          <div class="tile__pattern" aria-hidden="true"></div>
          <div class="tile__ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          </div>
          <div class="tile__big" data-i18n><span data-ar>التزام بالموعد</span><span data-en>On time, always</span></div>
          <p class="tile__d" data-i18n="block">
            <span data-ar>إشعارات عند انطلاق الفريق وعند وصوله — وتقييم سريع بعد كل زيارة يذهب مباشرة لفريق الجودة.</span>
            <span data-en>Alerts when the team sets off and arrives — plus a quick post-visit rating that reaches our quality team.</span>
          </p>
        </article>
      </div>
    </div>
  </section>

  <!-- WHY VELTO -->
  <section class="why" id="why">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>ما يميزنا</span><span data-en>What sets us apart</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>راحتك وجودة خدمتك <span class="hl">أولويتنا</span></span>
          <span data-en>Your comfort and quality, <span class="hl">first</span></span>
        </h2>
      </div>

      <div class="why-grid">
        <div class="feat" data-reveal>
          <div class="feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2 5 5 .8-3.6 3.5.9 5L12 13.8 7.7 16.3l.9-5L5 7.8 10 7z"/></svg></div>
          <h4 class="feat__t" data-i18n><span data-ar>تنظيف ولمعان</span><span data-en>Clean &amp; gloss</span></h4>
          <p class="feat__d" data-i18n><span data-ar>نتيجة تستحق النظر بعد كل زيارة.</span><span data-en>A finish you'll notice, every visit.</span></p>
        </div>
        <div class="feat" data-reveal data-delay="1">
          <div class="feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
          <h4 class="feat__t" data-i18n><span data-ar>فريق محترف</span><span data-en>Trained team</span></h4>
          <p class="feat__d" data-i18n><span data-ar>مدرّب ومُقيّم بعد كل زيارة.</span><span data-en>Vetted and graded after each visit.</span></p>
        </div>
        <div class="feat" data-reveal data-delay="2">
          <div class="feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
          <h4 class="feat__t" data-i18n><span data-ar>سرعة وجودة</span><span data-en>Speed &amp; quality</span></h4>
          <p class="feat__d" data-i18n><span data-ar>التزام بالمواعيد في كل مرة.</span><span data-en>Punctual, every single time.</span></p>
        </div>
        <div class="feat" data-reveal>
          <div class="feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
          <h4 class="feat__t" data-i18n><span data-ar>مواد آمنة</span><span data-en>Safe products</span></h4>
          <p class="feat__d" data-i18n><span data-ar>لطيفة على طلاء سيارتك ودائمة.</span><span data-en>Gentle on your paint, lasting shine.</span></p>
        </div>
        <div class="feat" data-reveal data-delay="1">
          <div class="feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h13l3-3v6l-3-3M3 6h10M3 18h10"/></svg></div>
          <h4 class="feat__t" data-i18n><span data-ar>خدمة متنقلة</span><span data-en>Mobile service</span></h4>
          <p class="feat__d" data-i18n><span data-ar>نصلك أينما كنت في الرياض.</span><span data-en>We reach you anywhere in Riyadh.</span></p>
        </div>
        <div class="feat" data-reveal data-delay="2">
          <div class="feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 100 20 10 10 0 000-20z"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01M15 9h.01"/></svg></div>
          <h4 class="feat__t" data-i18n><span data-ar>تجربة شخصية</span><span data-en>Personal experience</span></h4>
          <p class="feat__d" data-i18n><span data-ar>عناية تُشعرك بالخصوصية والراحة.</span><span data-en>Care that feels private and easy.</span></p>
        </div>
      </div>
    </div>
  </section>

  <!-- PACKAGES -->
  <section id="plans">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>الباقات</span><span data-en>Packages</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>باقة تناسب <span class="hl">كل سيارة</span></span>
          <span data-en>A package for <span class="hl">every car</span></span>
        </h2>
        <p class="section__sub" data-i18n="block">
          <span data-ar>اختر الباقة الأنسب لك — وتواصل معنا لمعرفة السعر الحالي في منطقتك.</span>
          <span data-en>Choose what fits — reach out for current pricing in your area.</span>
        </p>
      </div>

      <div class="plans">
        <div class="plan" data-reveal>
          <span class="plan__badge" data-i18n><span data-ar>الأساسية</span><span data-en>Essential</span></span>
          <h3 class="plan__name" data-i18n><span data-ar>غسيل خارجي</span><span data-en>Exterior wash</span></h3>
          <p class="plan__for" data-i18n><span data-ar>للمظهر السريع واللمعان اليومي</span><span data-en>For a quick refresh and daily shine</span></p>
          <ul class="plan__list">
            <li>{!! $check !!}<span data-i18n><span data-ar>غسيل خارجي كامل</span><span data-en>Full exterior wash</span></span></li>
            <li>{!! $check !!}<span data-i18n><span data-ar>تلميع الجنوط والإطارات</span><span data-en>Wheel &amp; tyre shine</span></span></li>
            <li>{!! $check !!}<span data-i18n><span data-ar>تجفيف وتلميع سريع</span><span data-en>Dry &amp; quick gloss</span></span></li>
            <li>{!! $check !!}<span data-i18n><span data-ar>~ ٤٥ دقيقة</span><span data-en>~ 45 minutes</span></span></li>
          </ul>
          <a href="#contact" class="btn btn--ghost" data-i18n><span data-ar>احجز الباقة</span><span data-en>Choose plan</span>{!! $arrow !!}</a>
        </div>

        <div class="plan plan--featured" data-reveal data-delay="1">
          <div class="plan__pattern" aria-hidden="true"></div>
          <span class="plan__badge" data-i18n><span data-ar>الأكثر طلبًا</span><span data-en>Most popular</span></span>
          <h3 class="plan__name" data-i18n><span data-ar>تنظيف متكامل</span><span data-en>Full detail</span></h3>
          <p class="plan__for" data-i18n><span data-ar>عناية شاملة من الداخل والخارج</span><span data-en>Complete inside-and-out care</span></p>
          <ul class="plan__list">
            <li>{!! $check !!}<span data-i18n><span data-ar>كل ما في الباقة الأساسية</span><span data-en>Everything in Essential</span></span></li>
            <li>{!! $check !!}<span data-i18n><span data-ar>تنظيف داخلي كامل</span><span data-en>Full interior clean</span></span></li>
            <li>{!! $check !!}<span data-i18n><span data-ar>المقاعد والأرضيات والطبلون</span><span data-en>Seats, floors &amp; dashboard</span></span></li>
            <li>{!! $check !!}<span data-i18n><span data-ar>معطّر ولمسة أخيرة</span><span data-en>Fragrance &amp; finishing touch</span></span></li>
          </ul>
          <a href="#contact" class="btn btn--white" data-i18n><span data-ar>احجز الباقة</span><span data-en>Choose plan</span>{!! $arrow !!}</a>
        </div>

        <div class="plan" data-reveal data-delay="2">
          <span class="plan__badge" data-i18n><span data-ar>حسب الطلب</span><span data-en>Tailored</span></span>
          <h3 class="plan__name" data-i18n><span data-ar>باقة مخصّصة</span><span data-en>Custom care</span></h3>
          <p class="plan__for" data-i18n><span data-ar>لعنايةٍ أعمق أو مواعيد متكررة</span><span data-en>For deeper care or recurring visits</span></p>
          <ul class="plan__list">
            <li>{!! $check !!}<span data-i18n><span data-ar>عناية مخصّصة لسيارتك</span><span data-en>Care tailored to your car</span></span></li>
            <li>{!! $check !!}<span data-i18n><span data-ar>مواعيد أسبوعية أو شهرية</span><span data-en>Weekly or monthly visits</span></span></li>
            <li>{!! $check !!}<span data-i18n><span data-ar>أولوية في الحجز</span><span data-en>Priority booking</span></span></li>
            <li>{!! $check !!}<span data-i18n><span data-ar>لعدة سيارات أو مجمّعات</span><span data-en>Multi-car &amp; compounds</span></span></li>
          </ul>
          <a href="#contact" class="btn btn--ghost" data-i18n><span data-ar>تواصل معنا</span><span data-en>Talk to us</span>{!! $arrow !!}</a>
        </div>
      </div>
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
          <span data-ar>حمّل تطبيق Velto لحجز موعدك في ثوانٍ، وتتبّع فريقنا وهو في طريقه إليك، وإدارة سياراتك ومحفظتك في مكان واحد.</span>
          <span data-en>Get the Velto app to book in seconds, track your detailer on the way, and manage your cars and wallet in one place.</span>
        </p>
        <div class="store">
          <a href="#" aria-label="App Store">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.365 1.43c0 1.14-.42 2.2-1.12 2.98-.85.94-2.2 1.66-3.32 1.57-.13-1.1.42-2.27 1.05-2.99.72-.82 2.02-1.48 3.06-1.56.02.16.03.32.03.4zM20.5 17.1c-.55 1.26-.82 1.82-1.53 2.94-.99 1.55-2.38 3.48-4.11 3.5-1.53.02-1.93-1-4-1-2.07 0-2.51.98-4.04 1.02-1.73.03-3.05-1.68-4.04-3.23C-.02 17.15-.42 12.6 1.32 10.2c1.03-1.44 2.66-2.35 4.19-2.35 1.56 0 2.54 1.02 3.83 1.02 1.25 0 2.01-1.02 3.83-1.02 1.36 0 2.8.74 3.83 2.02-3.36 1.84-2.82 6.63.67 8.23z"/></svg>
            <span><small data-i18n><span data-ar>حمّله من</span><span data-en>Download on the</span></small><strong>App Store</strong></span>
          </a>
          <a href="#" aria-label="Google Play">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3.6 2.3c-.3.3-.5.7-.5 1.3v16.8c0 .6.2 1 .5 1.3l.1.1L13 12.6v-.2L3.7 2.2l-.1.1zM17 15.6l-2.7-2.7L4.9 22c.3.3.9.4 1.5 0L17 15.6zM20.8 10.9l-2.9-1.7-2.9 2.9 2.9 2.9 2.9-1.7c.9-.5.9-1.9 0-2.4zM4.9 2c-.6-.4-1.2-.3-1.5 0l9.4 9.4 2.7-2.7L4.9 2z"/></svg>
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

  <!-- FAQ -->
  <section id="faq" style="background: var(--bg);">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>الأسئلة الشائعة</span><span data-en>FAQ</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>الإجابات التي <span class="hl">تبحث عنها</span></span>
          <span data-en>Answers you're <span class="hl">looking for</span></span>
        </h2>
      </div>

      <div class="faq" id="faqList">
        <div class="faq__item">
          <button class="faq__q">
            <span class="faq__num">01</span>
            <span class="faq__q-text" data-i18n><span data-ar>هل أحتاج إلى مكان مخصص لغسيل السيارة؟</span><span data-en>Do I need a special place to wash the car?</span></span>
            <span class="faq__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
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
            <span class="faq__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
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
            <span class="faq__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
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
            <span class="faq__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span>
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
  <section id="contact">
    <div class="container">
      <div class="section__head" data-reveal>
        <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>تواصل معنا</span><span data-en>Get in touch</span></span>
        <h2 class="section__title" data-i18n="block">
          <span data-ar>جاهزون <span class="hl">لخدمتك</span></span>
          <span data-en>We're <span class="hl">here to help</span></span>
        </h2>
        <p class="section__sub" data-i18n="block">
          <span data-ar>اترك بياناتك وسيتواصل معك فريقنا خلال دقائق.</span>
          <span data-en>Leave your details and our team will get back to you in minutes.</span>
        </p>
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
            <li>
              <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
              <span>
                <span class="v" data-i18n><span data-ar>الرياض، السعودية</span><span data-en>Riyadh, Saudi Arabia</span></span>
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

        <form class="contact-card" data-reveal data-delay="1" onsubmit="event.preventDefault(); this.querySelector('.form__success').style.display='block'; this.querySelector('.form__submit').setAttribute('disabled','');">
          <h3 data-i18n><span data-ar>أرسل لنا رسالة</span><span data-en>Send us a message</span></h3>
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
            <p class="form__success" style="display:none; margin: 6px 0 0; color: var(--mint-ink); background: var(--mint); padding: 12px 14px; border-radius: 12px; font-weight: 700;" data-i18n="block">
              <span data-ar>تم استلام رسالتك. سنتواصل معك قريبًا.</span>
              <span data-en>Got it — we'll be in touch shortly.</span>
            </p>
          </div>
        </form>
      </div>
    </div>
  </section>

</main>

<footer>
  <div class="container">
    <div class="footer__top">
      <div>
        <a href="#top" class="footer__brand"><img src="/img/logo-velto.png" alt="Velto"></a>
        <p class="footer__tagline" data-i18n="block">
          <span data-ar>عناية بالسيارات للأفراد. Velto تأتي إليك — في بيتك، في عملك، في يومك.</span>
          <span data-en>Mobile car-care for individuals. Velto comes to you — your home, your office, your everyday.</span>
        </p>
      </div>
      <div>
        <h4 class="footer__h" data-i18n><span data-ar>روابط</span><span data-en>Links</span></h4>
        <ul class="footer__list">
          <li><a href="#how" data-i18n><span data-ar>كيف نعمل</span><span data-en>How it works</span></a></li>
          <li><a href="#services" data-i18n><span data-ar>خدماتنا</span><span data-en>Services</span></a></li>
          <li><a href="#plans" data-i18n><span data-ar>الباقات</span><span data-en>Packages</span></a></li>
          <li><a href="#faq" data-i18n><span data-ar>الأسئلة الشائعة</span><span data-en>FAQ</span></a></li>
        </ul>
      </div>
      <div>
        <h4 class="footer__h" data-i18n><span data-ar>تواصل</span><span data-en>Contact</span></h4>
        <ul class="footer__list">
          <li class="tnum"><a href="tel:+966559809687" dir="ltr" style="display:inline-block;">+966 55 980 9687</a></li>
          <li><a href="mailto:info@velto.sa">info@velto.sa</a></li>
          <li><a href="https://velto.sa">velto.sa</a></li>
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
      btn.addEventListener('click', function () { applyLang(btn.getAttribute('data-set-lang')); });
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

  // -------- Sticky nav state --------
  (function () {
    var nav = document.getElementById('nav');
    var onScroll = function () {
      if (window.scrollY > 24) nav.classList.add('is-scrolled');
      else nav.classList.remove('is-scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  })();

  // -------- Mobile drawer --------
  (function () {
    var drawer = document.getElementById('drawer');
    var open = document.getElementById('menuBtn');
    var close = document.getElementById('drawerClose');
    if (!drawer || !open) return;
    var toggle = function (state) { drawer.classList.toggle('is-open', state); document.body.style.overflow = state ? 'hidden' : ''; };
    open.addEventListener('click', function () { toggle(true); });
    close.addEventListener('click', function () { toggle(false); });
    drawer.addEventListener('click', function (e) { if (e.target === drawer) toggle(false); });
    drawer.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { toggle(false); }); });
  })();

  // -------- Reveal-on-scroll --------
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.04 });
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

  // -------- Showcase slot toggle --------
  document.querySelectorAll('.showcase .slot').forEach(function (s) {
    s.addEventListener('click', function () {
      document.querySelectorAll('.showcase .slot').forEach(function (x) { x.classList.remove('is-active'); });
      s.classList.add('is-active');
    });
  });
</script>
</body>
</html>
