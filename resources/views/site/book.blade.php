@extends('site.layout')
@php $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>'; @endphp
@section('content')
<main class="page">
  <section class="page__hero" style="padding-bottom: 40px;">
    <div class="container">
      <span class="eyebrow eyebrow--pill" data-i18n><span data-ar>الحجز</span><span data-en>Booking</span></span>
      <h1 class="page__title" data-i18n="block"><span data-ar>احجز غسلتك</span><span data-en>Book a wash</span></h1>
      <p class="page__sub" data-i18n="block"><span data-ar>أربع خطوات وسيارتك تلمع عند بابك</span><span data-en>Four steps and your car shines at your door</span></p>
    </div>
  </section>

  <section class="page__body" x-data="bookingWizard(@js($preselect))" x-cloak>
    <div class="container">

      <template x-if="loading"><p class="empty"><span class="spinner"></span></p></template>
      <template x-if="!loading && error && step === 0"><p class="error" style="text-align:center" x-text="error"></p></template>

      <div x-show="!loading">
        {{-- progress --}}
        <div class="steps-bar">
          <template x-for="(s, i) in steps" :key="s">
            <button type="button" class="steps-bar__item" :class="{ 'is-done': i < step, 'is-active': i === step }" @click="goto(i)">
              <i></i><span x-text="Velto.t('book.step.' + s)"></span>
            </button>
          </template>
        </div>

        <div class="grid grid--side">
          <div>
            {{-- STEP 1: service + add-ons --}}
            <div x-show="steps[step] === 'service'" class="stack">
              <h2 class="h3" x-text="Velto.t('book.chooseService')"></h2>
              <div class="grid grid--2">
                <template x-for="s in services" :key="s.id">
                  <div class="card card--tight card--sel" :class="{ 'is-active': service && service.id === s.id }" @click="pickService(s)">
                    <div class="row row--between">
                      <strong x-text="Velto.loc(s, 'name')"></strong>
                      <span class="price tnum"><span x-text="s.price"></span><small>SAR</small></span>
                    </div>
                    <p class="muted small" style="margin: 6px 0 0;" x-text="Velto.loc(s, 'description')"></p>
                    <p class="muted small" style="margin: 6px 0 0;">⏱ <span x-text="s.duration_minutes"></span> <span x-text="Velto.t('common.min')"></span></p>
                  </div>
                </template>
              </div>
              <template x-if="service && service.add_ons && service.add_ons.length">
                <div>
                  <h3 class="h4" style="margin: 16px 0 4px;" x-text="Velto.t('book.addons')"></h3>
                  <p class="muted small" style="margin: 0 0 10px;" x-text="Velto.t('book.addonsSub')"></p>
                  <div class="stack">
                    <template x-for="a in service.add_ons" :key="a.id">
                      <label class="check" :class="{ 'is-active': hasAddon(a) }" @click.prevent="toggleAddon(a)">
                        <span class="check__box">{!! $check !!}</span>
                        <span style="flex:1" x-text="Velto.loc(a, 'name')"></span>
                        <span class="price tnum">+<span x-text="a.extra_price"></span></span>
                      </label>
                    </template>
                  </div>
                </div>
              </template>
            </div>

            {{-- STEP 2: vehicle --}}
            <div x-show="steps[step] === 'vehicle'" class="stack">
              <div class="row row--between">
                <h2 class="h3" x-text="Velto.t('book.chooseVehicle')"></h2>
                <button type="button" class="btn btn--outline btn--sm" @click="addingVehicle = !addingVehicle" x-text="Velto.t('book.addVehicle')"></button>
              </div>
              <p class="muted small" x-text="Velto.t('book.sizeNote')"></p>
              <template x-if="!vehicles.length && !addingVehicle"><p class="empty" x-text="Velto.t('book.noVehicles')"></p></template>
              <div class="grid grid--2">
                <template x-for="v in vehicles" :key="v.id">
                  <div class="card card--tight card--sel" :class="{ 'is-active': vehicle && vehicle.id === v.id }" @click="vehicle = v">
                    <strong x-text="(v.brand || '') + ' ' + (v.model || '')"></strong>
                    <p class="muted small" style="margin: 4px 0 0;"><span dir="ltr" x-text="v.plate"></span> <span x-show="v.color">· <span x-text="v.color"></span></span></p>
                    <template x-if="v.category"><p class="small" style="margin: 6px 0 0;"><span class="pill pill--gray" x-text="Velto.loc(v.category, 'name')"></span> <span class="price tnum small" x-text="v.category.price + ' ' + Velto.t('common.sar')"></span></p></template>
                  </div>
                </template>
              </div>
              <template x-if="addingVehicle">
                <form class="card stack" @submit.prevent="saveVehicle()">
                  <h3 class="h4" x-text="Velto.t('veh.new')"></h3>
                  <div class="grid grid--2">
                    <div class="field"><label x-text="Velto.t('veh.brand')"></label>
                      <select class="select" x-model="vform.brand">
                        <option value="" x-text="Velto.t('veh.pick')"></option>
                        <template x-for="b in brands" :key="b.id"><option :value="b.id" x-text="Velto.loc(b, 'name')"></option></template>
                        <option value="other" x-text="Velto.t('veh.other')"></option>
                      </select>
                    </div>
                    <div class="field"><label x-text="Velto.t('veh.model')"></label>
                      <template x-if="!brandIsOther() && brandModels().length">
                        <select class="select" x-model="vform.model">
                          <option value="" x-text="Velto.t('veh.pick')"></option>
                          <template x-for="m in brandModels()" :key="m.id"><option :value="m.name" x-text="Velto.loc(m, 'name')"></option></template>
                        </select>
                      </template>
                      <template x-if="brandIsOther() || !brandModels().length"><input class="input" x-model="vform.otherModel"></template>
                    </div>
                  </div>
                  <template x-if="brandIsOther()"><div class="field"><label x-text="Velto.t('veh.brand')"></label><input class="input" x-model="vform.otherBrand"></div></template>
                  <div class="grid grid--2">
                    <div class="field"><label x-text="Velto.t('veh.plate')"></label><input class="input input--ltr" x-model="vform.plate" placeholder="ABC 1234"></div>
                    <div class="field"><label x-text="Velto.t('veh.color')"></label>
                      <select class="select" x-model="vform.color">
                        <option value="" x-text="Velto.t('veh.pick')"></option>
                        <template x-for="c in colors" :key="c.id"><option :value="c.id" x-text="Velto.loc(c, 'name')"></option></template>
                      </select>
                    </div>
                  </div>
                  <p class="error" x-show="verror" x-text="verror"></p>
                  <div class="row"><button type="submit" class="btn btn--primary btn--sm" :class="{ 'is-loading': vbusy }" x-text="Velto.t('common.save')"></button><button type="button" class="btn btn--ghost btn--sm" @click="addingVehicle = false" x-text="Velto.t('common.cancel')"></button></div>
                </form>
              </template>
            </div>

            {{-- STEP 3: location --}}
            <div x-show="steps[step] === 'location'" class="stack">
              <h2 class="h3" x-text="Velto.t('book.whereTitle')"></h2>
              <p class="muted small" x-text="Velto.t('book.whereSub')"></p>
              <template x-if="addresses.length">
                <div>
                  <p class="h4" style="margin-bottom: 8px;" x-text="Velto.t('book.savedAddresses')"></p>
                  <div class="chips"><template x-for="a in addresses" :key="a.id"><button type="button" class="chip" @click="useAddress(a)" x-text="a.label"></button></template></div>
                </div>
              </template>
              <div style="position: relative;">
                <div id="bookMap" class="map"></div>
                <svg class="map-pin" width="40" height="48" viewBox="0 0 40 48"><path d="M20 46s16-14.5 16-26A16 16 0 004 20c0 11.5 16 26 16 26z" fill="#8863E5" stroke="#fff" stroke-width="3"/><circle cx="20" cy="20" r="6" fill="#fff"/></svg>
              </div>
              <div class="row row--between row--wrap">
                <div>
                  <span class="pill pill--ok" x-show="coverage && coverage.covered">✓ <span x-text="Velto.t('book.covered')"></span> <span x-show="coverage && coverage.area">· <span x-text="coverage && coverage.area ? Velto.loc(coverage.area, 'name') : ''"></span></span></span>
                  <span class="pill pill--danger" x-show="coverage && !coverage.covered" x-text="Velto.t('book.notCovered')"></span>
                  <span class="pill pill--gray" x-show="checking" x-text="Velto.t('book.checking')"></span>
                </div>
                <button type="button" class="btn btn--outline btn--sm" @click="locate()">📍 <span x-text="Velto.t('book.locate')"></span></button>
              </div>
              <p class="muted small" x-show="label" x-text="label"></p>
              <label class="check" :class="{ 'is-active': saveAddress }" @click.prevent="saveAddress = !saveAddress">
                <span class="check__box">{!! $check !!}</span><span x-text="Velto.t('book.saveAddress')"></span>
              </label>
              <template x-if="saveAddress"><input class="input" x-model="addressLabel" :placeholder="Velto.t('book.addressLabel')"></template>
            </div>

            {{-- STEP 4: time --}}
            <div x-show="steps[step] === 'time'" class="stack">
              <h2 class="h3" x-text="Velto.t('book.pickDay')"></h2>
              <template x-if="!days.length"><p class="empty" x-text="Velto.t('book.noDays')"></p></template>
              <div class="days">
                <template x-for="d in days" :key="d">
                  <button type="button" class="day" :class="{ 'is-active': day === d }" @click="day = d; slot = null">
                    <small x-text="Velto.dayLabel(d)"></small><strong x-text="d.slice(8)"></strong><small x-text="d.slice(5, 7) + '/' + d.slice(0, 4)"></small>
                  </button>
                </template>
              </div>
              <template x-if="day">
                <div>
                  <h3 class="h4" style="margin-top: 10px;" x-text="Velto.t('book.pickSlot')"></h3>
                  <div class="slots">
                    <template x-for="s in daySlots()" :key="s.id">
                      <button type="button" class="chip" :class="{ 'is-active': slot && slot.id === s.id }" :disabled="!s.available" @click="slot = s" x-text="Velto.clock(s.start)"></button>
                    </template>
                  </div>
                  <p class="muted small" x-show="!daySlots().length" x-text="Velto.t('book.noSlots')"></p>
                </div>
              </template>
            </div>

            {{-- STEP 5: review --}}
            <div x-show="steps[step] === 'review'" class="stack">
              <h2 class="h3" x-text="Velto.t('book.review')"></h2>
              <div class="card stack">
                <h3 class="h4" x-text="Velto.t('book.payment')"></h3>
                <template x-if="plans.length">
                  <label class="check" :class="{ 'is-active': payment === 'package' }" @click.prevent="payment = 'package'">
                    <span class="check__box">{!! $check !!}</span>
                    <span style="flex:1"><span x-text="Velto.t('book.plan')"></span> — <span x-text="plan ? Velto.loc(plan.wash_package, 'name') : ''"></span> <small class="muted">(<span x-text="plan ? plan.visits_remaining : 0"></span> <span x-text="Velto.t('book.planVisits')"></span>)</small></span>
                  </label>
                </template>
                <label class="check" :class="{ 'is-active': payment === 'wallet' }" @click.prevent="payment = 'wallet'">
                  <span class="check__box">{!! $check !!}</span>
                  <span style="flex:1"><span x-text="Velto.t('book.wallet')"></span> <small class="muted">· <span x-text="Velto.t('book.walletBalance')"></span> <span class="tnum" x-text="wallet ? Velto.money(wallet.balance) : '—'"></span></small></span>
                  <span class="pill pill--danger" x-show="payment === 'wallet' && !walletOk()" x-text="Velto.t('book.insufficient')"></span>
                </label>
                <label class="check" :class="{ 'is-active': payment === 'card' }" @click.prevent="payment = 'card'">
                  <span class="check__box">{!! $check !!}</span><span x-text="Velto.t('book.card')"></span>
                </label>
                <template x-if="payment === 'package' && addons.length">
                  <div class="field"><label x-text="Velto.t('book.addonsPaid')"></label>
                    <div class="chips">
                      <button type="button" class="chip" :class="{ 'is-active': addonsPayment === 'wallet' }" @click="addonsPayment = 'wallet'" x-text="Velto.t('book.wallet')"></button>
                      <button type="button" class="chip" :class="{ 'is-active': addonsPayment === 'card' }" @click="addonsPayment = 'card'" x-text="Velto.t('acc.pay.card')"></button>
                    </div>
                  </div>
                </template>
              </div>
              <div class="card stack">
                <div class="field"><label x-text="Velto.t('book.promo')"></label>
                  <div class="row"><input class="input input--ltr" x-model="promo" @keydown.enter.prevent="applyPromo()"><button type="button" class="btn btn--outline btn--sm" @click="applyPromo()" x-text="Velto.t('book.apply')"></button></div>
                  <p class="error" x-show="promoError" x-text="promoError"></p>
                  <p class="help" x-show="promoInfo" style="color: var(--mint-ink);">✓ <span x-text="promoInfo ? Velto.loc(promoInfo, 'description') : ''"></span></p>
                </div>
                <div class="field"><label x-text="Velto.t('book.notes')"></label><textarea class="textarea" style="min-height: 80px;" x-model="notes" :placeholder="Velto.t('book.notesHint')"></textarea></div>
              </div>
              <p class="error" x-show="error" x-text="error"></p>
            </div>

            <div class="wizard__actions">
              <button type="button" class="btn btn--ghost" x-show="step > 0" @click="back()" x-text="Velto.t('common.back')"></button>
              <span x-show="step === 0"></span>
              <button type="button" class="btn btn--primary" x-show="steps[step] !== 'review'" :disabled="!canNext()" @click="next()"><span x-text="Velto.t('common.next')"></span>{!! $arrow !!}</button>
              <button type="button" class="btn btn--primary" x-show="steps[step] === 'review'" :class="{ 'is-loading': busy }" @click="submit()"><span x-text="payment === 'card' ? Velto.t('book.submitPay') : Velto.t('book.submit')"></span>{!! $arrow !!}</button>
            </div>
          </div>

          {{-- summary --}}
          <aside class="card sticky" x-show="service">
            <h3 class="h4" style="margin-bottom: 8px;" x-text="service ? Velto.loc(service, 'name') : ''"></h3>
            <div class="summary__line"><span class="muted" x-text="Velto.t('book.step.vehicle')"></span><strong x-text="vehicle ? (vehicle.brand + ' ' + vehicle.model) : '—'"></strong></div>
            <div class="summary__line" x-show="vehicle && vehicle.category"><span class="muted" x-text="Velto.t('book.sizeBand')"></span><strong x-text="vehicle && vehicle.category ? Velto.loc(vehicle.category, 'name') : ''"></strong></div>
            <div class="summary__line"><span class="muted" x-text="Velto.t('book.step.location')"></span><strong x-text="label || (coverage && coverage.area ? Velto.loc(coverage.area, 'name') : '—')" style="text-align:end; max-width: 60%;"></strong></div>
            <div class="summary__line"><span class="muted" x-text="Velto.t('book.step.time')"></span><strong x-text="slot ? (Velto.dayLabel(slot.date) + ' \u2066' + slot.date + '\u2069 · ' + Velto.clock(slot.start)) : '—'"></strong></div>
            <div class="divider"></div>
            <div class="summary__line"><span x-text="Velto.loc(service || {}, 'name')"></span><span class="tnum" x-text="payment === 'package' ? Velto.t('common.free') : Velto.money(base())"></span></div>
            <template x-for="a in addons" :key="a.id"><div class="summary__line"><span x-text="Velto.loc(a, 'name')"></span><span class="tnum" x-text="Velto.money(a.extra_price)"></span></div></template>
            <div class="summary__line" x-show="discount() > 0"><span x-text="Velto.t('book.discount')"></span><span class="tnum" style="color: var(--mint-ink);" x-text="'−' + Velto.money(discount())"></span></div>
            <div class="summary__total"><span x-text="Velto.t('book.total')"></span><span class="price tnum" x-text="Velto.money(total())"></span></div>
          </aside>
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
@push('scripts')
<script async src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&language=ar"></script>
@endpush
