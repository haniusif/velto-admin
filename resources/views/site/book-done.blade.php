@extends('site.layout')
@section('content')
<main class="page">
  <section class="page__body" style="padding-top: clamp(40px, 8vw, 96px);" x-data="bookDone(@js($status), @js($kind), @js($appointment))" x-cloak>
    <div class="container">
      <div class="auth">
        <div class="card" style="text-align:center;">
          <template x-if="loading"><span class="spinner"></span></template>
          <template x-if="!loading">
            <div class="stack">
              <div style="font-size: 56px; line-height: 1;" x-text="status === 'success' ? '✅' : (status === 'pending' ? '⏳' : '❌')"></div>
              <h1 class="h3" x-text="title()"></h1>
              <p class="muted" x-text="sub()"></p>
              <template x-if="appt">
                <div class="kv" style="text-align:start; margin: 8px auto 0; max-width: 360px;">
                  <dt>#</dt><dd class="tnum" x-text="appt.id"></dd>
                  <dt x-text="Velto.t('book.step.service')"></dt><dd x-text="Velto.loc(appt.service, 'name')"></dd>
                  <dt x-text="Velto.t('book.step.time')"></dt><dd x-text="appt.time_slot ? (appt.time_slot.date + ' · ' + Velto.clock(appt.time_slot.start_time)) : ''"></dd>
                  <dt x-text="Velto.t('book.total')"></dt><dd class="tnum" x-text="Velto.money(appt.total_price)"></dd>
                </div>
              </template>
              <div class="row" style="justify-content:center; flex-wrap: wrap; margin-top: 8px;">
                <template x-if="kind === 'booking' && id"><a :href="'/account/bookings/' + id" class="btn btn--primary" x-text="Velto.t('book.viewBooking')"></a></template>
                <template x-if="kind === 'plan'"><a href="/account/plans" class="btn btn--primary" x-text="Velto.t('acc.plans')"></a></template>
                <template x-if="kind === 'wallet'"><a href="/account/wallet" class="btn btn--primary" x-text="Velto.t('acc.wallet')"></a></template>
                <a href="/book" class="btn btn--ghost" x-text="Velto.t('book.bookAnother')"></a>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
