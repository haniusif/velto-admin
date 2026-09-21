@extends('site.layout')
@php
  $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>';
  $icons = [
    'bookings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>',
    'vehicles' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13M5 13h14v4a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H8v1a1 1 0 01-1 1H6a1 1 0 01-1-1v-4z"/></svg>',
    'wallet' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="6" width="18" height="13" rx="3"/><path d="M16 12h5M3 10h18"/></svg>',
    'plans' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 5 5.6.6-4 4 1 5.4L12 19l-5 3 1-5.4-4-4 5.6-.6z"/></svg>',
    'profile' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg>',
    'support' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 14v-3a8 8 0 0116 0v3"/><rect x="3" y="13" width="4" height="6" rx="2"/><rect x="17" y="13" width="4" height="6" rx="2"/></svg>',
    'notifications' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 16V11a6 6 0 0112 0v5l2 2H4l2-2zM10 20a2 2 0 004 0"/></svg>',
  ];
  $cities = \App\Models\City::query()->orderBy('name')->get(['id', 'name', 'name_ar']);
  $whatsapp = $support['support.whatsapp'] ?? '966559809687';
  $phone = $support['support.phone'] ?? '+966559809687';
  $sent = request()->boolean('sent');
  $subscribe = (int) request()->query('subscribe', 0);
  $mode = $section === 'support' && $id === 'new' ? 'new' : null;
@endphp
@section('content')
<main class="page">
  <section class="page__body" style="padding-top: 36px;">
    <div class="container">
      <div class="account">
        <nav class="side-nav">
          @foreach($icons as $key => $svg)
          <a href="/account/{{ $key }}" class="{{ $section === $key ? 'is-active' : '' }}">{!! $svg !!}<span x-data x-text="Velto.t('acc.{{ $key }}')"></span></a>
          @endforeach
        </nav>

        <div>
          {{-- ===================== BOOKINGS ===================== --}}
          @if($section === 'bookings' && !$id)
          <div x-data="accountBookings()" x-cloak>
            <div class="row row--between" style="margin-bottom: 18px;">
              <h1 class="h3" x-text="Velto.t('acc.bookings')"></h1>
              <a href="/book" class="btn btn--primary btn--sm" x-text="Velto.t('nav.book')"></a>
            </div>
            <div class="chips" style="margin-bottom: 16px;">
              <button type="button" class="chip" :class="{ 'is-active': tab === 'upcoming' }" @click="tab = 'upcoming'" x-text="Velto.t('acc.upcoming')"></button>
              <button type="button" class="chip" :class="{ 'is-active': tab === 'past' }" @click="tab = 'past'" x-text="Velto.t('acc.past')"></button>
            </div>
            <template x-if="loading"><p class="empty"><span class="spinner"></span></p></template>
            <template x-if="!loading && !list().length">
              <div class="empty"><p x-text="Velto.t('acc.noBookings')"></p><a href="/book" class="btn btn--primary btn--sm" x-text="Velto.t('acc.bookNow')"></a></div>
            </template>
            <div class="list">
              <template x-for="a in list()" :key="a.id">
                <a :href="'/account/bookings/' + a.id" class="item">
                  <span class="item__icon">{!! $icons['bookings'] !!}</span>
                  <span class="item__body">
                    <strong><span x-text="Velto.loc(a.service, 'name')"></span> <span class="muted small">#<span x-text="a.id"></span></span></strong>
                    <small><span x-text="when(a)"></span> · <span x-text="a.vehicle ? a.vehicle.label : ''"></span></small>
                  </span>
                  <span class="pill" :class="pill(a.status)" x-text="Velto.t('acc.status.' + a.status)"></span>
                  <span class="price tnum" x-text="Velto.money(a.total_price)"></span>
                </a>
              </template>
            </div>
          </div>
          @endif

          {{-- ===================== BOOKING DETAIL ===================== --}}
          @if($section === 'bookings' && $id)
          <div x-data="bookingDetail(@js((int) $id))" x-cloak>
            <a href="/account/bookings" class="muted small">← <span x-text="Velto.t('acc.bookings')"></span></a>
            <template x-if="loading"><p class="empty"><span class="spinner"></span></p></template>
            <template x-if="error"><p class="error" x-text="error"></p></template>
            <template x-if="a">
              <div class="stack" style="margin-top: 12px;">
                <div class="card">
                  <div class="row row--between row--wrap">
                    <h1 class="h3"><span x-text="Velto.loc(a.service, 'name')"></span> <span class="muted">#<span x-text="a.id"></span></span></h1>
                    <span class="pill" :class="pill(a.status)" x-text="Velto.t('acc.status.' + a.status)"></span>
                  </div>
                  <dl class="kv" style="margin-top: 14px;">
                    <dt x-text="Velto.t('book.step.time')"></dt><dd x-text="when()"></dd>
                    <dt x-text="Velto.t('book.step.vehicle')"></dt><dd x-text="a.vehicle ? a.vehicle.label : '—'"></dd>
                    <dt x-text="Velto.t('book.step.location')"></dt><dd x-text="a.location && (a.location.label || (a.location.area && Velto.loc(a.location.area, 'name'))) || '—'"></dd>
                    <dt x-text="Velto.t('book.payment')"></dt><dd><span x-text="Velto.t('acc.pay.' + a.payment_method)"></span> · <span class="pill" :class="a.payment_status === 'paid' ? 'pill--ok' : (a.payment_status === 'refunded' ? 'pill--gray' : 'pill--warn')" x-text="a.payment_status === 'paid' ? Velto.t('acc.paid') : (a.payment_status === 'refunded' ? Velto.t('acc.refunded') : Velto.t('acc.unpaid'))"></span></dd>
                    <template x-if="a.notes"><dt x-text="Velto.t('book.notes')"></dt></template><template x-if="a.notes"><dd x-text="a.notes"></dd></template>
                  </dl>
                  <div class="divider"></div>
                  <div class="summary__line"><span x-text="Velto.loc(a.service, 'name')"></span><span class="tnum" x-text="Velto.money(a.base_price)"></span></div>
                  <template x-for="ad in a.add_ons" :key="ad.id"><div class="summary__line"><span x-text="Velto.loc(ad, 'name')"></span><span class="tnum" x-text="Velto.money(ad.extra_price)"></span></div></template>
                  <div class="summary__line" x-show="Number(a.discount_total) > 0"><span x-text="Velto.t('book.discount')"></span><span class="tnum">−<span x-text="Velto.money(a.discount_total)"></span></span></div>
                  <div class="summary__total"><span x-text="Velto.t('book.total')"></span><span class="price tnum" x-text="Velto.money(a.total_price)"></span></div>
                </div>

                <template x-if="tracking && tracking.worker">
                  <div class="card">
                    <h3 class="h4" x-text="Velto.t('acc.specialist')"></h3>
                    <p style="margin: 6px 0 0;"><strong x-text="tracking.worker.name"></strong> <a :href="'tel:' + tracking.worker.phone" dir="ltr" class="muted small" x-text="tracking.worker.phone"></a></p>
                  </div>
                </template>

                <div class="row row--wrap">
                  <button type="button" class="btn btn--primary btn--sm" x-show="a.can_pay" :class="{ 'is-loading': busy }" @click="pay()" x-text="Velto.t('acc.pay')"></button>
                  <button type="button" class="btn btn--outline btn--sm" x-show="a.can_reschedule && !rescheduling" @click="startReschedule()" x-text="Velto.t('acc.reschedule')"></button>
                  <button type="button" class="btn btn--danger btn--sm" x-show="a.can_cancel" :class="{ 'is-loading': busy }" @click="cancel()" x-text="Velto.t('acc.cancel')"></button>
                </div>

                <template x-if="rescheduling">
                  <div class="card stack">
                    <h3 class="h4" x-text="Velto.t('acc.reschedule')"></h3>
                    <div class="days"><template x-for="d in days" :key="d"><button type="button" class="day" :class="{ 'is-active': day === d }" @click="day = d; slot = null"><small x-text="Velto.dayLabel(d)"></small><strong x-text="d.slice(8)"></strong><small x-text="d.slice(5, 7)"></small></button></template></div>
                    <div class="slots"><template x-for="s in daySlots()" :key="s.id"><button type="button" class="chip" :class="{ 'is-active': slot && slot.id === s.id }" :disabled="!s.available" @click="slot = s" x-text="Velto.clock(s.start)"></button></template></div>
                    <div class="row"><button type="button" class="btn btn--primary btn--sm" :disabled="!slot" :class="{ 'is-loading': busy }" @click="reschedule()" x-text="Velto.t('common.confirm')"></button><button type="button" class="btn btn--ghost btn--sm" @click="rescheduling = false" x-text="Velto.t('common.cancel')"></button></div>
                  </div>
                </template>

                <template x-if="a.can_review || a.review">
                  <div class="card stack">
                    <h3 class="h4" x-text="a.review ? Velto.t('acc.rated') : Velto.t('acc.rate')"></h3>
                    <div class="stars"><template x-for="n in 5" :key="n"><button type="button" :class="{ 'is-on': n <= rating }" :disabled="!!a.review" @click="rating = n">★</button></template></div>
                    <template x-if="!a.review"><textarea class="textarea" style="min-height: 70px;" x-model="comment" :placeholder="Velto.t('acc.comment')"></textarea></template>
                    <template x-if="a.review && a.review.comment"><p class="muted" x-text="a.review.comment"></p></template>
                    <template x-if="!a.review"><div><button type="button" class="btn btn--primary btn--sm" :disabled="!rating" :class="{ 'is-loading': busy }" @click="review()" x-text="Velto.t('common.save')"></button></div></template>
                  </div>
                </template>
              </div>
            </template>
          </div>
          @endif

          {{-- ===================== VEHICLES ===================== --}}
          @if($section === 'vehicles')
          <div x-data="accountVehicles()" x-cloak>
            <div class="row row--between" style="margin-bottom: 18px;">
              <h1 class="h3" x-text="Velto.t('acc.vehicles')"></h1>
              <button type="button" class="btn btn--primary btn--sm" @click="startAdd()" x-text="Velto.t('veh.new')"></button>
            </div>
            <template x-if="loading"><p class="empty"><span class="spinner"></span></p></template>
            <template x-if="adding">
              <form class="card stack" style="margin-bottom: 18px;" @submit.prevent="save()">
                <h3 class="h4" x-text="editing ? Velto.t('common.edit') : Velto.t('veh.new')"></h3>
                <div class="grid grid--2">
                  <div class="field"><label x-text="Velto.t('veh.brand')"></label>
                    <select class="select" x-model="form.brand"><option value="" x-text="Velto.t('veh.pick')"></option><template x-for="b in brands" :key="b.id"><option :value="b.id" x-text="Velto.loc(b, 'name')"></option></template><option value="other" x-text="Velto.t('veh.other')"></option></select>
                  </div>
                  <div class="field"><label x-text="Velto.t('veh.model')"></label>
                    <template x-if="!isOther() && brandModels().length"><select class="select" x-model="form.model"><option value="" x-text="Velto.t('veh.pick')"></option><template x-for="m in brandModels()" :key="m.id"><option :value="m.name" x-text="Velto.loc(m, 'name')"></option></template></select></template>
                    <template x-if="isOther() || !brandModels().length"><input class="input" x-model="form.otherModel"></template>
                  </div>
                </div>
                <template x-if="isOther()"><div class="field"><label x-text="Velto.t('veh.brand')"></label><input class="input" x-model="form.otherBrand"></div></template>
                <div class="grid grid--3">
                  <div class="field"><label x-text="Velto.t('veh.plate')"></label><input class="input input--ltr" x-model="form.plate"></div>
                  <div class="field"><label x-text="Velto.t('veh.color')"></label><select class="select" x-model="form.color"><option value="" x-text="Velto.t('veh.pick')"></option><template x-for="c in colors" :key="c.id"><option :value="c.id" x-text="Velto.loc(c, 'name')"></option></template></select></div>
                  <div class="field"><label x-text="Velto.t('veh.name')"></label><input class="input" x-model="form.name"></div>
                </div>
                <p class="error" x-show="error" x-text="error"></p>
                <div class="row"><button type="submit" class="btn btn--primary btn--sm" :class="{ 'is-loading': busy }" x-text="Velto.t('common.save')"></button><button type="button" class="btn btn--ghost btn--sm" @click="adding = false" x-text="Velto.t('common.cancel')"></button></div>
              </form>
            </template>
            <div class="list">
              <template x-for="v in items" :key="v.id">
                <div class="item">
                  <span class="item__icon">{!! $icons['vehicles'] !!}</span>
                  <span class="item__body">
                    <strong><span x-text="(v.brand || '') + ' ' + (v.model || '')"></span> <span class="pill pill--mint" x-show="v.is_default" x-text="Velto.t('veh.default')"></span></strong>
                    <small><span dir="ltr" x-text="v.plate"></span> <span x-show="v.color">· <span x-text="v.color"></span></span> <span x-show="v.category">· <span x-text="v.category ? Velto.loc(v.category, 'name') : ''"></span></span></small>
                  </span>
                  <button type="button" class="btn btn--ghost btn--sm" x-show="!v.is_default" @click="makeDefault(v)" x-text="Velto.t('veh.makeDefault')"></button>
                  <button type="button" class="btn btn--ghost btn--sm" @click="startEdit(v)" x-text="Velto.t('common.edit')"></button>
                  <button type="button" class="btn btn--danger btn--sm" @click="remove(v)" x-text="Velto.t('common.delete')"></button>
                </div>
              </template>
            </div>
            <template x-if="!loading && !items.length"><p class="empty" x-text="Velto.t('book.noVehicles')"></p></template>
          </div>
          @endif

          {{-- ===================== WALLET ===================== --}}
          @if($section === 'wallet')
          <div x-data="accountWallet()" x-cloak class="stack">
            <h1 class="h3" x-text="Velto.t('acc.wallet')"></h1>
            <template x-if="loading"><p class="empty"><span class="spinner"></span></p></template>
            <template x-if="w">
              <div class="grid grid--side">
                <div>
                  <div class="stat"><div class="stat__lbl" x-text="Velto.t('wal.balance')"></div><div class="stat__num" x-text="Velto.money(w.balance)"></div></div>
                  <h3 class="h4" style="margin: 22px 0 10px;" x-text="Velto.t('wal.history')"></h3>
                  <template x-if="!w.transactions.length"><p class="empty" x-text="Velto.t('wal.empty')"></p></template>
                  <div class="list">
                    <template x-for="tx in w.transactions" :key="tx.id">
                      <div class="item">
                        <span class="item__body"><strong x-text="tx.note || kind(tx.kind)"></strong><small x-text="Velto.fmtDateTime(tx.at)"></small></span>
                        <span class="price tnum" :style="Number(tx.amount) < 0 ? 'color: var(--fg)' : 'color: var(--mint-ink)'" x-text="(Number(tx.amount) > 0 ? '+' : '') + Velto.money(tx.amount)"></span>
                      </div>
                    </template>
                  </div>
                </div>
                <form class="card stack" @submit.prevent="topup()">
                  <h3 class="h4" x-text="Velto.t('wal.topup')"></h3>
                  <div class="chips"><template x-for="n in [50, 100, 200, 500]" :key="n"><button type="button" class="chip tnum" :class="{ 'is-active': Number(amount) === n }" @click="amount = n" x-text="n"></button></template></div>
                  <div class="field"><label x-text="Velto.t('wal.amount')"></label><input class="input input--ltr" type="number" min="1" max="5000" x-model="amount"></div>
                  <p class="error" x-show="error" x-text="error"></p>
                  <button type="submit" class="btn btn--primary btn--block" :class="{ 'is-loading': busy }"><span x-text="Velto.t('book.card')"></span></button>
                </form>
              </div>
            </template>
          </div>
          @endif

          {{-- ===================== PLANS ===================== --}}
          @if($section === 'plans')
          <div x-data="accountPlans(@js($subscribe))" x-cloak class="stack">
            <h1 class="h3" x-text="Velto.t('plan.mine')"></h1>
            <template x-if="loading"><p class="empty"><span class="spinner"></span></p></template>
            <template x-if="!loading && !mine.length"><p class="empty" x-text="Velto.t('plan.none')"></p></template>
            <div class="list">
              <template x-for="p in mine" :key="p.id">
                <div class="item">
                  <span class="item__icon">{!! $icons['plans'] !!}</span>
                  <span class="item__body">
                    <strong><span x-text="Velto.loc(p.wash_package, 'name')"></span> <span class="pill" :class="statusPill(p.status)" x-text="Velto.t('plan.status.' + p.status)"></span></strong>
                    <small><span class="tnum" x-text="p.visits_used + '/' + p.visits_total"></span> <span x-text="Velto.t('plan.used')"></span><span x-show="p.expires_at"> · <span x-text="Velto.t('plan.expires')"></span> <span class="tnum" dir="ltr" x-text="(p.expires_at || '').slice(0, 10)"></span></span></small>
                  </span>
                  <a href="/book" class="btn btn--outline btn--sm" x-show="p.is_usable" x-text="Velto.t('plan.useIt')"></a>
                </div>
              </template>
            </div>

            <h2 class="h3" style="margin-top: 24px;" x-text="Velto.t('plan.title')"></h2>
            <div class="grid grid--3">
              <template x-for="p in catalog" :key="p.id">
                <div class="card card--tight card--sel" :class="{ 'is-active': sel && sel.id === p.id }" @click="sel = p">
                  <strong x-text="Velto.loc(p, 'name')"></strong>
                  <p class="muted small" style="margin: 4px 0 8px;" x-text="Velto.loc(p, 'description')"></p>
                  <div class="price tnum" style="font-size: 26px;"><span x-text="p.price"></span><small>SAR</small></div>
                  <p class="small muted" style="margin: 6px 0 0;"><span class="tnum" x-text="p.visits_count"></span> <span x-text="Velto.t('plan.visits')"></span> · <span class="tnum" x-text="p.validity_days"></span> <span x-text="Velto.t('plan.days')"></span></p>
                </div>
              </template>
            </div>
            <template x-if="sel">
              <div class="card stack">
                <h3 class="h4"><span x-text="Velto.t('plan.subscribe')"></span>: <span x-text="Velto.loc(sel, 'name')"></span> — <span class="price tnum" x-text="Velto.money(sel.price)"></span></h3>
                <div class="field"><label x-text="Velto.t('plan.chooseVehicle')"></label>
                  <div class="chips"><template x-for="v in vehicles" :key="v.id"><button type="button" class="chip" :class="{ 'is-active': vehicle && vehicle.id === v.id }" @click="vehicle = v" x-text="v.brand + ' ' + v.model + ' · ' + v.plate"></button></template></div>
                  <p class="help" x-show="!vehicles.length"><a href="/account/vehicles" x-text="Velto.t('book.addVehicle')"></a></p>
                </div>
                <div class="field"><label x-text="Velto.t('book.payment')"></label>
                  <div class="chips">
                    <button type="button" class="chip" :class="{ 'is-active': payment === 'wallet' }" @click="payment = 'wallet'"><span x-text="Velto.t('book.wallet')"></span> (<span class="tnum" x-text="wallet ? Velto.money(wallet.balance) : ''"></span>)</button>
                    <button type="button" class="chip" :class="{ 'is-active': payment === 'card' }" @click="payment = 'card'" x-text="Velto.t('acc.pay.card')"></button>
                  </div>
                </div>
                <p class="error" x-show="error" x-text="error"></p>
                <div><button type="button" class="btn btn--primary" :disabled="!vehicle" :class="{ 'is-loading': busy }" @click="subscribe()" x-text="Velto.t('plan.subscribe')"></button></div>
              </div>
            </template>
          </div>
          @endif

          {{-- ===================== PROFILE ===================== --}}
          @if($section === 'profile')
          <div x-data="accountProfile(@js($cities))" x-cloak class="stack">
            <h1 class="h3" x-text="Velto.t('acc.profile')"></h1>
            <form class="card stack" @submit.prevent="save()">
              <div class="field"><label x-text="Velto.t('profile.phone')"></label><input class="input input--ltr" :value="$store.auth.user ? $store.auth.user.phone : ''" disabled></div>
              <div class="field"><label x-text="Velto.t('profile.name')"></label><input class="input" x-model="form.name" required></div>
              <div class="field"><label x-text="Velto.t('profile.email')"></label><input class="input input--ltr" type="email" x-model="form.email"></div>
              <div class="field"><label x-text="Velto.t('profile.gender')"></label>
                <div class="chips"><button type="button" class="chip" :class="{ 'is-active': form.gender === 'male' }" @click="form.gender = 'male'" x-text="Velto.t('profile.male')"></button><button type="button" class="chip" :class="{ 'is-active': form.gender === 'female' }" @click="form.gender = 'female'" x-text="Velto.t('profile.female')"></button></div>
              </div>
              <div class="grid grid--2">
                <div class="field"><label x-text="Velto.t('profile.city')"></label><select class="select" x-model="form.city"><option value=""></option><template x-for="c in cities" :key="c.id"><option :value="c.name" x-text="Velto.loc(c, 'name')"></option></template></select></div>
                <div class="field"><label x-text="Velto.t('profile.area')"></label><select class="select" x-model="form.area" :disabled="!areas.length"><option value=""></option><template x-for="a in areas" :key="a.id"><option :value="a.name" x-text="Velto.loc(a, 'name')"></option></template></select></div>
              </div>
              <div class="field"><label x-text="Velto.t('profile.lang')"></label>
                <div class="chips"><button type="button" class="chip" :class="{ 'is-active': form.preferred_language === 'ar' }" @click="form.preferred_language = 'ar'">العربية</button><button type="button" class="chip" :class="{ 'is-active': form.preferred_language === 'en' }" @click="form.preferred_language = 'en'">English</button></div>
              </div>
              <p class="error" x-show="error" x-text="error"></p>
              <div class="row row--between row--wrap">
                <button type="submit" class="btn btn--primary btn--sm" :class="{ 'is-loading': busy }" x-text="Velto.t('common.save')"></button>
                <button type="button" class="btn btn--ghost btn--sm" @click="logout()" x-text="Velto.t('nav.logout')"></button>
              </div>
            </form>
            <p class="small"><a href="/delete-account" class="muted" data-i18n><span data-ar>حذف الحساب</span><span data-en>Delete account</span></a></p>
          </div>
          @endif

          {{-- ===================== SUPPORT ===================== --}}
          @if($section === 'support')
          <div x-data="accountSupport(@js($mode ? null : ($id ? (int) $id : null)), @js($mode))" x-cloak class="stack">
            <template x-if="mode === 'list'">
              <div class="stack">
                <h1 class="h3" x-text="Velto.t('sup.title')"></h1>
                <div class="grid grid--2">
                  <a href="https://wa.me/{{ $whatsapp }}" class="card card--tight card--sel"><strong>💬 <span x-text="Velto.t('sup.whatsapp')"></span></strong><p class="muted small" style="margin: 4px 0 0;" dir="ltr">+{{ $whatsapp }}</p></a>
                  <a href="tel:{{ $phone }}" class="card card--tight card--sel"><strong>📞 <span x-text="Velto.t('sup.call')"></span></strong><p class="muted small" style="margin: 4px 0 0;" dir="ltr">{{ $phone }}</p></a>
                  <a href="/account/support/new" class="card card--tight card--sel"><strong>🎫 <span x-text="Velto.t('sup.new')"></span></strong></a>
                  <a href="/account/support/new?type=suggestion" class="card card--tight card--sel"><strong>💡 <span x-text="Velto.t('sup.suggest')"></span></strong></a>
                  <a href="/faq" class="card card--tight card--sel"><strong>❓ <span x-text="Velto.t('nav.faq')"></span></strong></a>
                </div>
                <h2 class="h4" style="margin-top: 10px;" x-text="Velto.t('sup.mine')"></h2>
                <template x-if="loading"><p class="empty"><span class="spinner"></span></p></template>
                <template x-if="!loading && !items.length"><p class="empty" x-text="Velto.t('sup.empty')"></p></template>
                <div class="list">
                  <template x-for="t in items" :key="t.id">
                    <a :href="'/account/support/' + t.id" class="item">
                      <span class="item__icon">{!! $icons['support'] !!}</span>
                      <span class="item__body"><strong x-text="t.subject"></strong><small><span x-text="Velto.t('sup.t.' + t.type)"></span> · <span x-text="(t.created_at || '').slice(0, 10)"></span></small></span>
                      <span class="pill" :class="pill(t.status)" x-text="Velto.t('sup.s.' + t.status)"></span>
                    </a>
                  </template>
                </div>
              </div>
            </template>

            <template x-if="mode === 'new'">
              <form class="card stack" @submit.prevent="send()" x-init="form.type = new URLSearchParams(location.search).get('type') || 'complaint'">
                <h1 class="h3" x-text="form.type === 'suggestion' ? Velto.t('sup.suggest') : Velto.t('sup.new')"></h1>
                <div class="field"><label x-text="Velto.t('sup.type')"></label>
                  <div class="chips"><template x-for="ty in ['complaint','suggestion','inquiry','other']" :key="ty"><button type="button" class="chip" :class="{ 'is-active': form.type === ty }" @click="form.type = ty" x-text="Velto.t('sup.t.' + ty)"></button></template></div>
                </div>
                <div class="field"><label x-text="Velto.t('sup.subject')"></label><input class="input" x-model="form.subject" maxlength="160"></div>
                <div class="field"><label x-text="Velto.t('sup.message')"></label><textarea class="textarea" x-model="form.message" maxlength="4000"></textarea></div>
                <template x-if="form.type !== 'suggestion' && bookings.length">
                  <div class="field"><label x-text="Velto.t('sup.booking')"></label>
                    <select class="select" x-model="form.appointment_id"><option value="" x-text="Velto.t('sup.none')"></option><template x-for="b in bookings" :key="b.id"><option :value="b.id" x-text="'#' + b.id + ' · ' + Velto.loc(b.service, 'name') + ' · ' + (b.time_slot ? b.time_slot.date : '')"></option></template></select>
                  </div>
                </template>
                <p class="error" x-show="error" x-text="error"></p>
                <div class="row"><button type="submit" class="btn btn--primary btn--sm" :class="{ 'is-loading': busy }" x-text="Velto.t('sup.send')"></button><a href="/account/support" class="btn btn--ghost btn--sm" x-text="Velto.t('common.cancel')"></a></div>
              </form>
            </template>

            <template x-if="mode === 'detail'">
              <div class="stack">
                <a href="/account/support" class="muted small">← <span x-text="Velto.t('sup.mine')"></span></a>
                @if($sent)<p class="pill pill--ok" style="display:inline-flex;" x-text="Velto.t('sup.sent')"></p>@endif
                <template x-if="loading"><p class="empty"><span class="spinner"></span></p></template>
                <template x-if="t">
                  <div class="stack">
                    <div class="card">
                      <div class="row row--between"><span class="pill pill--gray" x-text="Velto.t('sup.t.' + t.type)"></span><span class="pill" :class="pill(t.status)" x-text="Velto.t('sup.s.' + t.status)"></span></div>
                      <h1 class="h3" style="margin-top: 8px;" x-text="t.subject"></h1>
                      <template x-if="t.appointment_id"><p class="small"><a :href="'/account/bookings/' + t.appointment_id" style="color: var(--purple); font-weight: 700;">#<span x-text="t.appointment_id"></span></a></p></template>
                    </div>
                    <div class="card"><div class="row row--between"><strong x-text="Velto.t('sup.yours')"></strong><small class="muted tnum" x-text="Velto.fmtDateTime(t.created_at)"></small></div><p style="margin: 10px 0 0; white-space: pre-line;" x-text="t.message"></p></div>
                    <template x-if="t.admin_reply"><div class="card" style="background: #EEF8F3; border-color: #BFE3D1;"><div class="row row--between"><strong x-text="Velto.t('sup.reply')"></strong><small class="muted tnum" x-text="Velto.fmtDateTime(t.replied_at)"></small></div><p style="margin: 10px 0 0; white-space: pre-line;" x-text="t.admin_reply"></p></div></template>
                    <template x-if="!t.admin_reply"><p class="pill pill--gray" style="display:inline-flex;" x-text="Velto.t('sup.awaiting')"></p></template>
                  </div>
                </template>
              </div>
            </template>
          </div>
          @endif

          {{-- ===================== NOTIFICATIONS ===================== --}}
          @if($section === 'notifications')
          <div x-data="accountNotifications()" x-cloak class="stack">
            <div class="row row--between"><h1 class="h3" x-text="Velto.t('acc.notifications')"></h1><button type="button" class="btn btn--ghost btn--sm" @click="readAll()" x-text="Velto.t('notif.readAll')"></button></div>
            <template x-if="loading"><p class="empty"><span class="spinner"></span></p></template>
            <template x-if="!loading && !items.length"><p class="empty" x-text="Velto.t('notif.empty')"></p></template>
            <div class="list">
              <template x-for="n in items" :key="n.id">
                <button type="button" class="item" style="text-align:start; width:100%;" :style="!n.is_read ? 'border-color: var(--lilac); background: rgba(136,99,229,0.05)' : ''" @click="open(n)">
                  <span class="item__icon">{!! $icons['notifications'] !!}</span>
                  <span class="item__body"><strong x-text="title(n)"></strong><small x-text="body(n)"></small></span>
                  <small class="muted tnum" x-text="Velto.ago(n.created_at)"></small>
                </button>
              </template>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </section>
</main>
@endsection
