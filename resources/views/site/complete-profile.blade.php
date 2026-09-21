@extends('site.layout')
@section('content')
<main class="page">
  <section class="page__body" style="padding-top: clamp(40px, 8vw, 96px);">
    <div class="container">
      <div class="auth" x-data="completeProfile(@js($next), @js($cities))" x-cloak>
        <div class="card">
          <form @submit.prevent="save()" class="stack">
            <div>
              <h1 class="h3" x-text="Velto.t('profile.title')"></h1>
              <p class="muted" style="margin: 6px 0 0;" x-text="Velto.t('profile.sub')"></p>
            </div>
            <div class="field">
              <label x-text="Velto.t('profile.name')"></label>
              <input class="input" type="text" x-model="form.name" required minlength="2">
            </div>
            <div class="field">
              <label><span x-text="Velto.t('profile.gender')"></span> <span class="muted small">(<span x-text="Velto.t('common.optional')"></span>)</span></label>
              <div class="chips">
                <button type="button" class="chip" :class="{ 'is-active': form.gender === 'male' }" @click="form.gender = form.gender === 'male' ? '' : 'male'" x-text="Velto.t('profile.male')"></button>
                <button type="button" class="chip" :class="{ 'is-active': form.gender === 'female' }" @click="form.gender = form.gender === 'female' ? '' : 'female'" x-text="Velto.t('profile.female')"></button>
              </div>
            </div>
            <div class="field">
              <label><span x-text="Velto.t('profile.email')"></span> <span class="muted small">(<span x-text="Velto.t('common.optional')"></span>)</span></label>
              <input class="input input--ltr" type="email" x-model="form.email" placeholder="you@example.com">
            </div>
            <div class="grid grid--2">
              <div class="field">
                <label x-text="Velto.t('profile.city')"></label>
                <select class="select" x-model="form.city">
                  <option value=""></option>
                  <template x-for="c in cities" :key="c.id"><option :value="c.name" x-text="Velto.loc(c, 'name')"></option></template>
                </select>
              </div>
              <div class="field">
                <label x-text="Velto.t('profile.area')"></label>
                <select class="select" x-model="form.area" :disabled="!areas.length">
                  <option value=""></option>
                  <template x-for="a in areas" :key="a.id"><option :value="a.name" x-text="Velto.loc(a, 'name')"></option></template>
                </select>
              </div>
            </div>
            <p class="error" x-show="error" x-text="error"></p>
            <button type="submit" class="btn btn--primary btn--block" :class="{ 'is-loading': busy }" x-text="Velto.t('profile.continue')"></button>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
