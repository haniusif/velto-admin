@extends('site.layout')
@section('content')
<main class="page">
  <section class="page__body" style="padding-top: clamp(40px, 8vw, 96px);">
    <div class="container">
      <div class="auth" x-data="loginForm(@js($next))" x-cloak>
        <div class="auth__logo"><img src="/img/logo-velto.png" alt="Velto"></div>
        <div class="card">
          {{-- Step 1: phone --}}
          <template x-if="step === 'phone'">
            <form @submit.prevent="send()" class="stack">
              <div>
                <h1 class="h3" x-text="Velto.t('auth.title')"></h1>
                <p class="muted" style="margin: 6px 0 0;" x-text="Velto.t('auth.sub')"></p>
              </div>
              <div class="field">
                <label x-text="Velto.t('auth.phone')"></label>
                <div class="phone-wrap">
                  <span class="prefix">+966</span>
                  <input class="input input--ltr" type="tel" inputmode="numeric" autocomplete="tel-national" placeholder="5xxxxxxxx" x-model="phone" autofocus>
                </div>
                <p class="error" x-show="error" x-text="error"></p>
              </div>
              <button type="submit" class="btn btn--primary btn--block" :class="{ 'is-loading': busy }">
                <span x-show="!busy" x-text="Velto.t('auth.send')"></span><span class="spinner" x-show="busy" style="border-top-color:#fff"></span>
              </button>
              <p class="help" style="text-align:center;">
                <span x-text="Velto.t('auth.terms')"></span>
                <a href="/terms" style="color: var(--purple); font-weight: 700;" x-text="Velto.t('auth.termsLink')"></a>
                <span x-text="Velto.t('auth.and')"></span>
                <a href="/privacy" style="color: var(--purple); font-weight: 700;" x-text="Velto.t('auth.privacyLink')"></a>
              </p>
            </form>
          </template>

          {{-- Step 2: code --}}
          <template x-if="step === 'code'">
            <form @submit.prevent="verify()" class="stack">
              <div>
                <h1 class="h3" x-text="Velto.t('auth.code')"></h1>
                <p class="muted" style="margin: 6px 0 0;"><span x-text="Velto.t('auth.codeSub')"></span> <b dir="ltr" x-text="normalized()"></b></p>
              </div>
              <div class="field">
                <input class="input input--ltr input--code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="4" x-model="code" x-ref="code" @input="onCodeInput()">
                <p class="error" x-show="error" x-text="error"></p>
              </div>
              <button type="submit" class="btn btn--primary btn--block" :class="{ 'is-loading': busy }">
                <span x-show="!busy" x-text="Velto.t('auth.verify')"></span><span class="spinner" x-show="busy" style="border-top-color:#fff"></span>
              </button>
              <div class="row row--between">
                <button type="button" class="btn btn--ghost btn--sm" @click="step = 'phone'; code = ''; error = ''" x-text="Velto.t('auth.change')"></button>
                <button type="button" class="btn btn--ghost btn--sm" :disabled="countdown > 0" @click="send()">
                  <span x-show="countdown > 0"><span x-text="Velto.t('auth.resendIn')"></span> <span class="tnum" x-text="countdown"></span></span>
                  <span x-show="countdown <= 0" x-text="Velto.t('auth.resend')"></span>
                </button>
              </div>
            </form>
          </template>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
