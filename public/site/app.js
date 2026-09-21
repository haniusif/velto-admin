/* Velto website — a client of the same /api/v1 the mobile app uses.
 * Auth is the app's Sanctum token, kept in localStorage; every screen here
 * calls the endpoints the app calls, so behaviour cannot drift between the two.
 */
(function () {
  'use strict';

  var API = '/api/v1/';
  var TOKEN_KEY = 'velto.token';

  // ---------------------------------------------------------------- i18n
  var T = {
    // nav / common
    'nav.services': ['خدماتنا', 'Services'], 'nav.plans': ['الباقات', 'Plans'], 'nav.faq': ['الأسئلة', 'FAQ'],
    'nav.coverage': ['مناطق التغطية', 'Coverage'], 'nav.contact': ['تواصل', 'Contact'], 'nav.login': ['تسجيل الدخول', 'Sign in'],
    'nav.account': ['حسابي', 'My account'], 'nav.book': ['احجز الآن', 'Book now'], 'nav.logout': ['تسجيل الخروج', 'Sign out'],
    'common.next': ['التالي', 'Next'], 'common.back': ['رجوع', 'Back'], 'common.save': ['حفظ', 'Save'], 'common.cancel': ['إلغاء', 'Cancel'],
    'common.close': ['إغلاق', 'Close'], 'common.loading': ['جارٍ التحميل…', 'Loading…'], 'common.retry': ['إعادة المحاولة', 'Retry'],
    'common.sar': ['ر.س', 'SAR'], 'common.min': ['دقيقة', 'min'], 'common.free': ['مجاناً', 'Free'], 'common.delete': ['حذف', 'Delete'],
    'common.edit': ['تعديل', 'Edit'], 'common.add': ['إضافة', 'Add'], 'common.confirm': ['تأكيد', 'Confirm'], 'common.optional': ['اختياري', 'optional'],
    'common.required': ['هذا الحقل مطلوب', 'This field is required'], 'common.error': ['حدث خطأ، حاول مرة أخرى.', 'Something went wrong. Please try again.'],
    'common.network': ['تحقق من اتصالك بالإنترنت وحاول مرة أخرى.', 'Check your connection and try again.'],
    'common.saved': ['تم الحفظ', 'Saved'], 'common.done': ['تم', 'Done'], 'common.yes': ['نعم', 'Yes'], 'common.no': ['لا', 'No'],
    // auth
    'auth.title': ['سجّل دخولك', 'Sign in'], 'auth.sub': ['أدخل رقم جوالك وسنرسل لك رمز التحقق', 'Enter your mobile number and we\'ll text you a code'],
    'auth.phone': ['رقم الجوال', 'Mobile number'], 'auth.send': ['إرسال الرمز', 'Send code'], 'auth.code': ['رمز التحقق', 'Verification code'],
    'auth.codeSub': ['أدخل الرمز المرسل إلى', 'Enter the code sent to'], 'auth.verify': ['تحقق', 'Verify'], 'auth.resend': ['إعادة الإرسال', 'Resend'],
    'auth.resendIn': ['إعادة الإرسال خلال', 'Resend in'], 'auth.change': ['تغيير الرقم', 'Change number'], 'auth.invalidPhone': ['أدخل رقم جوال سعودي صحيح (05xxxxxxxx)', 'Enter a valid Saudi mobile (05xxxxxxxx)'],
    'auth.invalidCode': ['الرمز غير صحيح', 'Incorrect code'], 'auth.terms': ['بالمتابعة أنت توافق على', 'By continuing you agree to the'], 'auth.termsLink': ['الشروط والأحكام', 'Terms of Service'],
    'auth.and': ['و', 'and'], 'auth.privacyLink': ['سياسة الخصوصية', 'Privacy Policy'],
    'auth.smsFailed': ['تعذر إرسال الرسالة، حاول بعد قليل.', 'Could not send the SMS. Try again shortly.'], 'auth.tooMany': ['محاولات كثيرة، انتظر قليلاً.', 'Too many attempts. Please wait a moment.'],
    // profile
    'profile.title': ['خطوة أخيرة', 'One last step'], 'profile.sub': ['أخبرنا باسمك حتى نخصّص لك التجربة', 'Tell us your name so we can personalise things'],
    'profile.name': ['الاسم الكامل', 'Full name'], 'profile.email': ['البريد الإلكتروني', 'Email'], 'profile.gender': ['الجنس', 'Gender'],
    'profile.male': ['ذكر', 'Male'], 'profile.female': ['أنثى', 'Female'], 'profile.city': ['المدينة', 'City'], 'profile.area': ['الحي', 'District'],
    'profile.continue': ['متابعة', 'Continue'], 'profile.lang': ['اللغة المفضلة', 'Preferred language'], 'profile.phone': ['رقم الجوال', 'Mobile'],
    // booking
    'book.title': ['احجز غسلتك', 'Book a wash'], 'book.sub': ['أربع خطوات وسيارتك تلمع عند بابك', 'Four steps and your car shines at your door'],
    'book.step.service': ['الخدمة', 'Service'], 'book.step.vehicle': ['السيارة', 'Vehicle'], 'book.step.location': ['الموقع', 'Location'],
    'book.step.time': ['الوقت', 'Time'], 'book.step.review': ['المراجعة', 'Review'],
    'book.chooseService': ['اختر الخدمة', 'Choose a service'], 'book.addons': ['إضافات', 'Add-ons'], 'book.addonsSub': ['اختر ما تحتاجه', 'Pick what you need'],
    'book.chooseVehicle': ['اختر السيارة', 'Choose a vehicle'], 'book.addVehicle': ['أضف سيارة', 'Add a vehicle'], 'book.noVehicles': ['لا توجد سيارات بعد — أضف سيارتك الأولى.', 'No vehicles yet — add your first.'],
    'book.sizeNote': ['السعر يعتمد على حجم السيارة', 'Price depends on the vehicle size'],
    'book.whereTitle': ['أين سيارتك؟', 'Where is your car?'], 'book.whereSub': ['حرّك الخريطة لتحديد الموقع بدقة', 'Move the map to pin the exact spot'],
    'book.covered': ['ضمن نطاق التغطية', 'Inside our coverage'], 'book.notCovered': ['خارج نطاق التغطية حالياً', 'Outside our coverage for now'],
    'book.checking': ['جارٍ التحقق من التغطية…', 'Checking coverage…'], 'book.locate': ['موقعي الحالي', 'Use my location'],
    'book.savedAddresses': ['العناوين المحفوظة', 'Saved addresses'], 'book.saveAddress': ['احفظ هذا العنوان', 'Save this address'], 'book.addressLabel': ['اسم العنوان (مثال: المنزل)', 'Address label (e.g. Home)'],
    'book.pickDay': ['اختر اليوم', 'Pick a day'], 'book.pickSlot': ['اختر الوقت', 'Pick a time'], 'book.noSlots': ['لا توجد مواعيد متاحة في هذا اليوم', 'No slots available on this day'],
    'book.noDays': ['لا توجد مواعيد متاحة حالياً — تواصل معنا عبر واتساب.', 'No slots open right now — reach us on WhatsApp.'],
    'book.review': ['راجع حجزك', 'Review your booking'], 'book.payment': ['طريقة الدفع', 'Payment method'], 'book.wallet': ['المحفظة', 'Wallet'],
    'book.card': ['بطاقة (مدى / فيزا / ماستركارد)', 'Card (mada / Visa / Mastercard)'], 'book.plan': ['من باقتي', 'From my plan'],
    'book.walletBalance': ['الرصيد', 'Balance'], 'book.insufficient': ['الرصيد غير كافٍ', 'Insufficient balance'],
    'book.promo': ['كود الخصم', 'Promo code'], 'book.apply': ['تطبيق', 'Apply'], 'book.promoInvalid': ['الكود غير صالح', 'Invalid code'],
    'book.notes': ['ملاحظات للفني', 'Notes for the specialist'], 'book.notesHint': ['رقم الموقف، لون البوابة، أي تفاصيل تساعدنا', 'Parking spot, gate colour, anything that helps'],
    'book.subtotal': ['المجموع', 'Subtotal'], 'book.discount': ['الخصم', 'Discount'], 'book.total': ['الإجمالي', 'Total'],
    'book.submit': ['تأكيد الحجز', 'Confirm booking'], 'book.submitPay': ['تأكيد والدفع', 'Confirm & pay'],
    'book.confirmedTitle': ['تم تأكيد حجزك 🎉', 'Your booking is confirmed 🎉'], 'book.confirmedSub': ['سنرسل لك إشعاراً عند انطلاق الفني', 'We\'ll notify you when the specialist is on the way'],
    'book.pendingTitle': ['بانتظار الدفع', 'Awaiting payment'], 'book.pendingSub': ['لم يكتمل الدفع. يمكنك المحاولة مرة أخرى من صفحة الحجز.', 'Payment didn\'t go through. You can retry from the booking page.'],
    'book.failedTitle': ['لم تتم عملية الدفع', 'Payment failed'], 'book.viewBooking': ['عرض الحجز', 'View booking'], 'book.bookAnother': ['حجز آخر', 'Book another'],
    'book.loginFirst': ['سجّل دخولك لإكمال الحجز', 'Sign in to complete your booking'],
    'book.addonsPaid': ['دفع الإضافات', 'Add-ons paid by'], 'book.planVisits': ['زيارات متبقية', 'visits left'], 'book.sizeBand': ['فئة الحجم', 'Size band'],
    // vehicles
    'veh.title': ['سياراتي', 'My vehicles'], 'veh.new': ['سيارة جديدة', 'New vehicle'], 'veh.name': ['اسم مختصر', 'Nickname'], 'veh.brand': ['الماركة', 'Brand'],
    'veh.model': ['الموديل', 'Model'], 'veh.color': ['اللون', 'Colour'], 'veh.plate': ['رقم اللوحة', 'Plate'], 'veh.default': ['السيارة الافتراضية', 'Default vehicle'],
    'veh.makeDefault': ['اجعلها الافتراضية', 'Make default'], 'veh.other': ['أخرى', 'Other'], 'veh.pick': ['اختر…', 'Choose…'], 'veh.deleteConfirm': ['حذف هذه السيارة؟', 'Delete this vehicle?'],
    // account
    'acc.bookings': ['حجوزاتي', 'My bookings'], 'acc.vehicles': ['سياراتي', 'My vehicles'], 'acc.wallet': ['المحفظة', 'Wallet'], 'acc.plans': ['باقاتي', 'My plans'],
    'acc.profile': ['الملف الشخصي', 'Profile'], 'acc.support': ['الدعم', 'Support'], 'acc.notifications': ['الإشعارات', 'Notifications'],
    'acc.upcoming': ['القادمة', 'Upcoming'], 'acc.past': ['السابقة', 'Past'], 'acc.noBookings': ['لا توجد حجوزات بعد', 'No bookings yet'],
    'acc.bookNow': ['احجز غسلتك الأولى', 'Book your first wash'], 'acc.details': ['التفاصيل', 'Details'],
    'acc.cancel': ['إلغاء الحجز', 'Cancel booking'], 'acc.cancelConfirm': ['هل تريد إلغاء هذا الحجز؟', 'Cancel this booking?'], 'acc.reschedule': ['تغيير الموعد', 'Reschedule'],
    'acc.pay': ['ادفع الآن', 'Pay now'], 'acc.rate': ['قيّم الخدمة', 'Rate the service'], 'acc.rated': ['تقييمك', 'Your rating'], 'acc.comment': ['تعليق', 'Comment'],
    'acc.specialist': ['الفني', 'Specialist'], 'acc.track': ['التتبع', 'Tracking'],
    'acc.status.pending': ['قيد التأكيد', 'Pending'], 'acc.status.confirmed': ['مؤكد', 'Confirmed'], 'acc.status.assigned': ['تم الإسناد', 'Assigned'],
    'acc.status.on_the_way': ['في الطريق', 'On the way'], 'acc.status.arrived': ['وصل', 'Arrived'], 'acc.status.in_progress': ['جارٍ التنفيذ', 'In progress'],
    'acc.status.completed': ['مكتمل', 'Completed'], 'acc.status.cancelled': ['ملغي', 'Cancelled'], 'acc.status.awaiting_payment': ['بانتظار الدفع', 'Awaiting payment'],
    'acc.status.no_show': ['لم يحضر', 'No show'],
    'acc.pay.wallet': ['المحفظة', 'Wallet'], 'acc.pay.card': ['بطاقة', 'Card'], 'acc.pay.package': ['باقة', 'Plan'], 'acc.pay.apple_pay': ['Apple Pay', 'Apple Pay'],
    'acc.paid': ['مدفوع', 'Paid'], 'acc.unpaid': ['غير مدفوع', 'Unpaid'], 'acc.refunded': ['مسترد', 'Refunded'],
    // wallet
    'wal.balance': ['رصيد المحفظة', 'Wallet balance'], 'wal.topup': ['شحن الرصيد', 'Top up'], 'wal.amount': ['المبلغ', 'Amount'], 'wal.history': ['العمليات', 'Transactions'],
    'wal.empty': ['لا توجد عمليات بعد', 'No transactions yet'], 'wal.kind.topup': ['شحن', 'Top-up'], 'wal.kind.booking': ['حجز', 'Booking'], 'wal.kind.refund': ['استرداد', 'Refund'], 'wal.kind.adjustment': ['تسوية', 'Adjustment'],
    // plans
    'plan.title': ['الباقات', 'Plans'], 'plan.sub': ['غسلات بسعر أقل ومواعيد أولوية', 'Cheaper washes and priority slots'], 'plan.subscribe': ['اشترك', 'Subscribe'],
    'plan.visits': ['زيارات', 'visits'], 'plan.days': ['يوم', 'days'], 'plan.valid': ['صالحة لمدة', 'Valid for'], 'plan.mine': ['باقاتي الحالية', 'My current plans'],
    'plan.none': ['لا توجد باقات فعّالة', 'No active plans'], 'plan.used': ['مستخدم', 'used'], 'plan.expires': ['تنتهي', 'Expires'], 'plan.chooseVehicle': ['اختر السيارة للباقة', 'Choose the vehicle for this plan'],
    'plan.status.active': ['فعّالة', 'Active'], 'plan.status.expired': ['منتهية', 'Expired'], 'plan.status.exhausted': ['مستهلكة', 'Used up'], 'plan.status.pending_payment': ['بانتظار الدفع', 'Awaiting payment'], 'plan.status.cancelled': ['ملغاة', 'Cancelled'],
    'plan.useIt': ['احجز من الباقة', 'Book with plan'],
    // support
    'sup.title': ['مركز المساعدة', 'Help Center'], 'sup.new': ['فتح تذكرة', 'Open a ticket'], 'sup.suggest': ['أرسل اقتراح', 'Send a suggestion'], 'sup.mine': ['تذاكري', 'My tickets'],
    'sup.type': ['النوع', 'Type'], 'sup.subject': ['الموضوع', 'Subject'], 'sup.message': ['التفاصيل', 'Details'], 'sup.booking': ['الحجز المرتبط', 'Related booking'],
    'sup.none': ['بدون حجز', 'No booking'], 'sup.send': ['إرسال', 'Send'], 'sup.sent': ['شكراً لك! استلمنا طلبك وسنرد عليك قريباً.', 'Thanks! We received your request and will get back to you.'],
    'sup.empty': ['لا توجد تذاكر بعد', 'No tickets yet'], 'sup.reply': ['رد فيلتو', 'Reply from Velto'], 'sup.yours': ['رسالتك', 'Your message'],
    'sup.awaiting': ['سنرد عليك في أقرب وقت، وسيصلك إشعار عند الرد.', 'We\'ll reply as soon as possible. You\'ll get a notification when we do.'],
    'sup.t.complaint': ['شكوى', 'Complaint'], 'sup.t.suggestion': ['اقتراح', 'Suggestion'], 'sup.t.inquiry': ['استفسار', 'Inquiry'], 'sup.t.other': ['أخرى', 'Other'],
    'sup.s.open': ['مفتوحة', 'Open'], 'sup.s.in_progress': ['قيد المعالجة', 'In progress'], 'sup.s.resolved': ['تم الحل', 'Resolved'], 'sup.s.closed': ['مغلقة', 'Closed'],
    'sup.whatsapp': ['واتساب', 'WhatsApp'], 'sup.call': ['اتصل بنا', 'Call us'],
    // notifications
    'notif.empty': ['لا توجد إشعارات', 'No notifications'], 'notif.readAll': ['تعليم الكل كمقروء', 'Mark all read'],
    'time.minAgo': ['قبل {n} د', '{n}m ago'], 'time.hourAgo': ['قبل {n} س', '{n}h ago'], 'time.dayAgo': ['قبل {n} ي', '{n}d ago'],
    'day.0': ['الأحد', 'Sun'], 'day.1': ['الاثنين', 'Mon'], 'day.2': ['الثلاثاء', 'Tue'], 'day.3': ['الأربعاء', 'Wed'], 'day.4': ['الخميس', 'Thu'], 'day.5': ['الجمعة', 'Fri'], 'day.6': ['السبت', 'Sat'],
    'day.today': ['اليوم', 'Today'], 'day.tomorrow': ['غداً', 'Tomorrow'],
  };

  function lang() { return document.documentElement.getAttribute('data-lang') === 'en' ? 'en' : 'ar'; }
  function t(key, vars) {
    var l = Alpine.store('ui').lang;
    var row = T[key];
    var s = row ? row[l === 'en' ? 1 : 0] : String(key).split('.').pop().replace(/_/g, ' ');
    if (vars) Object.keys(vars).forEach(function (k) { s = s.replace('{' + k + '}', vars[k]); });
    return s;
  }
  /** Pick the localized field off an API object: name / name_ar. */
  function loc(obj, field) {
    if (!obj) return '';
    var l = Alpine.store('ui').lang;
    var ar = obj[field + '_ar'];
    return (l === 'ar' && ar) ? ar : (obj[field] || ar || '');
  }

  // ---------------------------------------------------------------- api
  function token() { try { return localStorage.getItem(TOKEN_KEY); } catch (e) { return null; } }
  function setToken(v) { try { v ? localStorage.setItem(TOKEN_KEY, v) : localStorage.removeItem(TOKEN_KEY); } catch (e) {} }

  function ApiError(status, body) {
    this.status = status; this.body = body || {};
    this.message = (body && body.message) || '';
    this.code = body && body.code;
    this.errors = (body && body.errors) || {};
  }
  ApiError.prototype.text = function () {
    if (this.status === 0) return t('common.network');
    if (this.code === 'sms_send_failed') return t('auth.smsFailed');
    if (this.code === 'invalid_code') return t('auth.invalidCode');
    if (this.code === 'too_many_requests' || this.status === 429) return t('auth.tooMany');
    var first = Object.keys(this.errors)[0];
    if (first && this.errors[first] && this.errors[first][0]) return this.errors[first][0];
    if (this.status >= 500) return t('common.error');
    return this.message || t('common.error');
  };

  function api(path, opts) {
    opts = opts || {};
    var headers = { 'Accept': 'application/json', 'Accept-Language': lang() };
    if (opts.body !== undefined && !(opts.body instanceof FormData)) headers['Content-Type'] = 'application/json';
    var tk = token();
    if (tk && opts.auth !== false) headers['Authorization'] = 'Bearer ' + tk;
    var url = API + path.replace(/^\//, '');
    if (opts.query) {
      var q = Object.keys(opts.query).filter(function (k) { return opts.query[k] !== undefined && opts.query[k] !== null && opts.query[k] !== ''; })
        .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(opts.query[k]); }).join('&');
      if (q) url += (url.indexOf('?') >= 0 ? '&' : '?') + q;
    }
    return fetch(url, {
      method: opts.method || (opts.body !== undefined ? 'POST' : 'GET'),
      headers: headers,
      body: opts.body === undefined ? undefined : (opts.body instanceof FormData ? opts.body : JSON.stringify(opts.body)),
    }).then(function (res) {
      return res.text().then(function (txt) {
        var body = null; try { body = txt ? JSON.parse(txt) : {}; } catch (e) { body = { message: txt }; }
        if (res.status === 401 && tk) { setToken(null); Alpine.store('auth').user = null; }
        if (!res.ok) throw new ApiError(res.status, body);
        return body;
      });
    }, function () { throw new ApiError(0, {}); });
  }

  // ---------------------------------------------------------------- format
  function money(n, cur) {
    var v = Math.round((Number(n) || 0) * 100) / 100;
    var s = (v % 1 === 0) ? String(v) : v.toFixed(2);
    return s + ' ' + (cur === 'SAR' || !cur ? t('common.sar') : cur);
  }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function dateStr(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
  function parseDate(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); }
  /** "16:00" → "4:00 PM" / "4:00 م" — the same rule as the app. */
  function clock(hhmm) {
    if (!hhmm) return '';
    var p = hhmm.split(':'); var h = +p[0], m = +p[1];
    if (isNaN(h) || isNaN(m)) return hhmm;
    var h12 = h % 12 === 0 ? 12 : h % 12;
    var ampm = h < 12 ? (lang() === 'ar' ? 'ص' : 'AM') : (lang() === 'ar' ? 'م' : 'PM');
    return h12 + ':' + pad(m) + ' ' + ampm;
  }
  function dayLabel(s) {
    var d = parseDate(s), today = new Date(); today.setHours(0, 0, 0, 0);
    var diff = Math.round((d - today) / 86400000);
    if (diff === 0) return t('day.today');
    if (diff === 1) return t('day.tomorrow');
    return t('day.' + d.getDay());
  }
  function fmtDateTime(iso) {
    if (!iso) return '';
    var d = new Date(iso);
    return '\u2066' + dateStr(d) + '\u2069 · ' + clock(pad(d.getHours()) + ':' + pad(d.getMinutes()));
  }
  function ago(iso) {
    var diff = (Date.now() - new Date(iso).getTime()) / 60000;
    if (diff < 60) return t('time.minAgo', { n: Math.max(1, Math.round(diff)) });
    if (diff < 1440) return t('time.hourAgo', { n: Math.round(diff / 60) });
    return t('time.dayAgo', { n: Math.round(diff / 1440) });
  }
  /** "+9665…" → "05…" for display. */
  function localPhone(p) { return (p || '').replace(/^\+966/, '0'); }

  // ---------------------------------------------------------------- stores
  document.addEventListener('alpine:init', function () {
    Alpine.store('ui', {
      lang: lang(),
      toast: null,
      _toastTimer: null,
      setLang: function (l) {
        var html = document.documentElement;
        html.setAttribute('lang', l); html.setAttribute('dir', l === 'ar' ? 'rtl' : 'ltr'); html.setAttribute('data-lang', l);
        document.querySelectorAll('[data-set-lang]').forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-set-lang') === l); });
        try { localStorage.setItem('velto.lang', l); } catch (e) {}
        this.lang = l;
      },
      notify: function (msg) {
        var self = this; self.toast = msg; clearTimeout(self._toastTimer);
        self._toastTimer = setTimeout(function () { self.toast = null; }, 3200);
      },
    });

    Alpine.store('auth', {
      user: null,
      ready: false,
      get loggedIn() { return !!this.user; },
      get initial() { var n = (this.user && this.user.name) || ''; return n ? n.trim().charAt(0).toUpperCase() : '👤'; },
      init: function () {
        var self = this;
        if (!token()) { self.ready = true; return; }
        api('auth/me').then(function (r) { self.user = r.data; }).catch(function () {}).finally(function () { self.ready = true; });
      },
      logout: function () {
        var self = this;
        api('auth/logout', { method: 'POST', body: {} }).catch(function () {}).finally(function () {
          setToken(null); self.user = null; window.location.href = '/';
        });
      },
      /** Bounce to login, coming back here afterwards. */
      require: function () {
        if (this.user) return true;
        window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname + window.location.search);
        return false;
      },
    });
  });

  // Language toggle buttons work before Alpine boots too.
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-set-lang]').forEach(function (btn) {
      btn.addEventListener('click', function () { Alpine.store('ui').setLang(btn.getAttribute('data-set-lang')); });
    });
  });

  // ---------------------------------------------------------------- components
  window.Velto = { t: t, loc: loc, api: api, money: money, clock: clock, dayLabel: dayLabel, fmtDateTime: fmtDateTime, ago: ago, localPhone: localPhone, token: token, setToken: setToken, dateStr: dateStr };

  /** Phone → OTP → (complete profile) → next. Same endpoints as the app. */
  window.loginForm = function (next) {
    return {
      step: 'phone', phone: '', code: '', busy: false, error: '', countdown: 0, _timer: null,
      next: next || '/account',
      init: function () { if (Alpine.store('auth').user) window.location.replace(this.next); },
      normalized: function () {
        var d = this.phone.replace(/\D/g, '');
        if (d.indexOf('966') === 0) d = d.slice(3);
        if (d.indexOf('0') === 0) d = d.slice(1);
        return d.length === 9 && d.charAt(0) === '5' ? '+966' + d : null;
      },
      send: function () {
        var self = this, p = self.normalized();
        self.error = '';
        if (!p) { self.error = t('auth.invalidPhone'); return; }
        self.busy = true;
        api('auth/request-otp', { body: { phone: p }, auth: false }).then(function () {
          self.step = 'code'; self.startCountdown();
          self.$nextTick(function () { var el = self.$refs.code; if (el) el.focus(); });
        }).catch(function (e) { self.error = e.text(); }).finally(function () { self.busy = false; });
      },
      startCountdown: function () {
        var self = this; self.countdown = 60; clearInterval(self._timer);
        self._timer = setInterval(function () { if (--self.countdown <= 0) clearInterval(self._timer); }, 1000);
      },
      verify: function () {
        var self = this;
        if (self.code.replace(/\D/g, '').length !== 4) { self.error = t('auth.invalidCode'); return; }
        self.busy = true; self.error = '';
        api('auth/verify-otp', { body: { phone: self.normalized(), code: self.code.replace(/\D/g, '') }, auth: false }).then(function (r) {
          var d = r.data || {};
          var tk = d.token || (d.access_token) || (d.auth && d.auth.token);
          setToken(tk);
          var customer = d.customer || d.user || d;
          Alpine.store('auth').user = customer;
          if (customer && customer.profile_completed === false) {
            window.location.href = '/complete-profile?next=' + encodeURIComponent(self.next);
          } else {
            window.location.href = self.next;
          }
        }).catch(function (e) { self.error = e.text(); }).finally(function () { self.busy = false; });
      },
      onCodeInput: function () { if (this.code.replace(/\D/g, '').length === 4) this.verify(); },
    };
  };

  window.completeProfile = function (next, cities) {
    return {
      next: next || '/account', cities: cities || [], areas: [],
      form: { name: '', email: '', gender: '', city: '', area: '', preferred_language: lang() },
      busy: false, error: '',
      init: function () {
        var self = this, u = Alpine.store('auth').user;
        if (u) { self.form.name = u.name || ''; self.form.email = u.email || ''; self.form.gender = u.gender || ''; self.form.city = u.city || ''; self.form.area = u.area || ''; }
        self.$watch('$store.auth.user', function (u) { if (u && !self.form.name) { self.form.name = u.name || ''; } });
        self.$watch('form.city', function () { self.loadAreas(); });
        self.loadAreas();
      },
      loadAreas: function () {
        var self = this, c = self.cities.find(function (x) { return x.name === self.form.city || x.name_ar === self.form.city; });
        if (!c) { self.areas = []; return; }
        api('locations/cities/' + c.id + '/areas', { auth: false }).then(function (r) { self.areas = r.data || []; }).catch(function () { self.areas = []; });
      },
      save: function () {
        var self = this;
        if (!self.form.name || self.form.name.trim().length < 2) { self.error = t('common.required'); return; }
        self.busy = true; self.error = '';
        var body = { name: self.form.name.trim(), preferred_language: self.form.preferred_language };
        if (self.form.email) body.email = self.form.email.trim();
        if (self.form.gender) body.gender = self.form.gender;
        if (self.form.city) body.city = self.form.city;
        if (self.form.area) body.area = self.form.area;
        api('auth/profile', { method: 'PATCH', body: body }).then(function (r) {
          Alpine.store('auth').user = r.data; Alpine.store('ui').notify(t('common.saved'));
          if (self.next) window.location.href = self.next;
        }).catch(function (e) { self.error = e.text(); }).finally(function () { self.busy = false; });
      },
    };
  };
})();

/* ---------------------------------------------------------------- booking wizard */
(function () {
  var V = window.Velto;

  function normalizeSlot(s) {
    return { id: s.id, date: s.date, start: (s.start_time || '').slice(0, 5), end: (s.end_time || '').slice(0, 5), available: !s.is_full };
  }

  window.bookingWizard = function (opts) {
    opts = opts || {};
    return {
      step: 0, steps: ['service', 'vehicle', 'location', 'time', 'review'],
      loading: true, error: '', busy: false,
      // catalog
      services: [], service: null, addons: [],
      brands: [], colors: [],
      // vehicle
      vehicles: [], vehicle: null, addingVehicle: false,
      vform: { brand: '', model: '', otherBrand: '', otherModel: '', color: '', plate: '', name: '' }, vbusy: false, verror: '',
      // location
      map: null, geocoder: null, lat: 24.7136, lng: 46.6753, label: '', coverage: null, checking: false, addresses: [], saveAddress: false, addressLabel: '',
      // time
      slots: [], days: [], day: null, slot: null,
      // review
      payment: 'wallet', wallet: null, plans: [], plan: null, addonsPayment: 'wallet', promo: '', promoInfo: null, promoError: '', notes: '',
      result: null,

      init: function () {
        var self = this;
        var auth = Alpine.store('auth');
        var boot = function () {
          if (!auth.user) { window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname + window.location.search); return; }
          self.load();
        };
        if (auth.ready) boot(); else self.$watch('$store.auth.ready', function (r) { if (r) boot(); });
      },

      load: function () {
        var self = this;
        Promise.all([
          V.api('catalog/wash-packages', { auth: false }), V.api('me/vehicles'), V.api('me/addresses'),
          V.api('catalog/vehicle-brands', { auth: false }), V.api('catalog/vehicle-colors', { auth: false }),
          V.api('me/wallet'), V.api('me/packages'),
        ]).then(function (r) {
          self.services = (r[0].data || []).filter(function (s) { return (s.type || 'single') === 'single'; });
          self.vehicles = r[1].data || [];
          self.addresses = r[2].data || [];
          self.brands = r[3].data || []; self.colors = r[4].data || [];
          self.wallet = r[5].data || null;
          self.plans = (r[6].data || []).filter(function (p) { return p.is_usable; });
          if (opts.service) self.service = self.services.find(function (s) { return s.id === opts.service; }) || null;
          var def = self.vehicles.find(function (v) { return v.is_default; }) || self.vehicles[0];
          if (def) self.vehicle = def;
          if (self.plans.length) { self.plan = self.plans[0]; }
          if (!self.wallet || Number(self.wallet.balance) <= 0) self.payment = 'card';
          if (self.service && opts.service) self.step = 1;
          self.loading = false;
        }).catch(function (e) { self.error = e.text ? e.text() : V.t('common.error'); self.loading = false; });
      },

      // --------------------------------------------------------- navigation
      canNext: function () {
        switch (this.steps[this.step]) {
          case 'service': return !!this.service;
          case 'vehicle': return !!this.vehicle;
          case 'location': return !!(this.coverage && this.coverage.covered);
          case 'time': return !!this.slot;
          default: return false;
        }
      },
      next: function () {
        if (!this.canNext()) return;
        this.step++;
        var s = this.steps[this.step];
        if (s === 'location') this.$nextTick(this.initMap.bind(this));
        if (s === 'time') this.loadSlots();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      },
      back: function () { if (this.step > 0) { this.step--; if (this.steps[this.step] === 'location') this.$nextTick(this.initMap.bind(this)); window.scrollTo({ top: 0, behavior: 'smooth' }); } },
      goto: function (i) { if (i < this.step) { this.step = i; if (this.steps[i] === 'location') this.$nextTick(this.initMap.bind(this)); } },

      // --------------------------------------------------------- service
      pickService: function (s) { this.service = s; this.addons = []; },
      toggleAddon: function (a) {
        var i = this.addons.findIndex(function (x) { return x.id === a.id; });
        if (i >= 0) this.addons.splice(i, 1); else this.addons.push(a);
      },
      hasAddon: function (a) { return this.addons.some(function (x) { return x.id === a.id; }); },

      // --------------------------------------------------------- vehicle
      brandModels: function () {
        var b = this.brands.find(function (x) { return String(x.id) === String(this.vform.brand); }.bind(this));
        return b ? (b.models || []) : [];
      },
      brandIsOther: function () { return this.vform.brand === 'other'; },
      saveVehicle: function () {
        var self = this; self.verror = '';
        var b = self.brands.find(function (x) { return String(x.id) === String(self.vform.brand); });
        var brand = self.brandIsOther() ? self.vform.otherBrand.trim() : (b ? b.name : '');
        var model = (self.brandIsOther() || !self.brandModels().length) ? self.vform.otherModel.trim() : self.vform.model;
        if (!brand || !model || !self.vform.plate.trim()) { self.verror = V.t('common.required'); return; }
        var c = self.colors.find(function (x) { return String(x.id) === String(self.vform.color); });
        self.vbusy = true;
        V.api('me/vehicles', { body: { name: self.vform.name.trim() || null, brand: brand, model: model, color: c ? c.name : null, plate: self.vform.plate.trim().toUpperCase() } })
          .then(function (r) { self.vehicles.unshift(r.data); self.vehicle = r.data; self.addingVehicle = false; self.vform = { brand: '', model: '', otherBrand: '', otherModel: '', color: '', plate: '', name: '' }; })
          .catch(function (e) { self.verror = e.text(); }).finally(function () { self.vbusy = false; });
      },

      // --------------------------------------------------------- location
      initMap: function () {
        var self = this, el = document.getElementById('bookMap');
        if (!el || !window.google || !google.maps) { setTimeout(self.initMap.bind(self), 300); return; }
        if (self.map) { google.maps.event.trigger(self.map, 'resize'); return; }
        self.map = new google.maps.Map(el, { center: { lat: self.lat, lng: self.lng }, zoom: 13, disableDefaultUI: true, zoomControl: true, gestureHandling: 'greedy', styles: [{ featureType: 'poi', stylers: [{ visibility: 'off' }] }] });
        self.geocoder = new google.maps.Geocoder();
        var t;
        self.map.addListener('idle', function () {
          var c = self.map.getCenter(); self.lat = c.lat(); self.lng = c.lng();
          clearTimeout(t); t = setTimeout(function () { self.checkCoverage(); self.reverse(); }, 350);
        });
        if (!self.coverage) self.checkCoverage();
      },
      checkCoverage: function () {
        var self = this; self.checking = true;
        V.api('catalog/coverage/check', { query: { lat: self.lat, lng: self.lng }, auth: false })
          .then(function (r) { self.coverage = r.data; }).catch(function () { self.coverage = { covered: false }; }).finally(function () { self.checking = false; });
      },
      reverse: function () {
        var self = this; if (!self.geocoder) return;
        self.geocoder.geocode({ location: { lat: self.lat, lng: self.lng }, language: Alpine.store('ui').lang }, function (res, status) {
          if (status === 'OK' && res && res[0]) {
            // Neighbourhood + street reads better than the full postal line.
            var parts = res[0].address_components || [];
            var pick = function (type) { var c = parts.find(function (p) { return p.types.indexOf(type) >= 0; }); return c ? c.long_name : null; };
            self.label = [pick('route'), pick('sublocality') || pick('neighborhood'), pick('locality')].filter(Boolean).join('، ') || res[0].formatted_address;
          }
        });
      },
      locate: function () {
        var self = this; if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(function (pos) {
          self.map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude }); self.map.setZoom(16);
        }, function () { Alpine.store('ui').notify(V.t('common.error')); }, { enableHighAccuracy: true, timeout: 8000 });
      },
      useAddress: function (a) { this.label = a.label; this.map.setCenter({ lat: a.lat, lng: a.lng }); this.map.setZoom(16); },

      // --------------------------------------------------------- time
      loadSlots: function () {
        var self = this; self.slots = []; self.days = []; self.slot = null;
        V.api('catalog/availability', { auth: false }).then(function (r) {
          self.slots = (r.data || []).map(normalizeSlot);
          var seen = {}; self.days = [];
          self.slots.forEach(function (s) { if (!seen[s.date]) { seen[s.date] = true; self.days.push(s.date); } });
          self.day = self.days[0] || null;
        }).catch(function () {});
      },
      daySlots: function () { var d = this.day; return this.slots.filter(function (s) { return s.date === d; }); },

      // --------------------------------------------------------- pricing
      base: function () {
        if (!this.service) return 0;
        var band = this.vehicle && this.vehicle.category && this.vehicle.category.price;
        return Number(band != null ? band : this.service.price) || 0;
      },
      addonsTotal: function () { return this.addons.reduce(function (s, a) { return s + Number(a.extra_price || 0); }, 0); },
      subtotal: function () { return (this.payment === 'package' ? 0 : this.base()) + this.addonsTotal(); },
      discount: function () { return this.promoInfo ? Number(this.promoInfo.discount || 0) : 0; },
      total: function () { return Math.max(0, this.subtotal() - this.discount()); },
      walletOk: function () { return this.wallet && Number(this.wallet.balance) >= this.total(); },
      applyPromo: function () {
        var self = this; self.promoError = ''; self.promoInfo = null;
        if (!self.promo.trim()) return;
        V.api('me/promo/preview', { body: { code: self.promo.trim(), subtotal: self.subtotal() } })
          .then(function (r) { self.promoInfo = r.data; }).catch(function (e) { self.promoError = e.text() || V.t('book.promoInvalid'); });
      },

      // --------------------------------------------------------- submit
      submit: function () {
        var self = this; if (self.busy) return;
        var method = self.payment;
        if (method === 'wallet' && !self.walletOk()) { self.error = V.t('book.insufficient'); return; }
        var body = {
          vehicle_id: self.vehicle.id, wash_package_id: self.service.id, time_slot_id: self.slot.id,
          add_on_ids: self.addons.map(function (a) { return a.id; }), payment_method: method,
          location: { label: self.label || null, lat: self.lat, lng: self.lng },
        };
        if (method === 'package') { body.customer_package_id = self.plan.id; if (self.addons.length) body.addons_payment_method = self.addonsPayment; }
        if (self.promoInfo) body.promo_code = self.promoInfo.code;
        if (self.notes.trim()) body.notes = self.notes.trim();
        self.busy = true; self.error = '';
        var after = function (r) {
          var d = r.data || {}; var appt = d.appointment || {}; var pay = d.payment || {};
          if (self.saveAddress && self.addressLabel.trim()) {
            V.api('me/addresses', { body: { label: self.addressLabel.trim(), subtitle: self.label || null, lat: self.lat, lng: self.lng, is_covered: true, icon_key: 'place' } }).catch(function () {});
          }
          if (pay.payment_page_url) {
            document.cookie = 'velto_web_pay=booking:' + appt.id + '; path=/; max-age=1800; SameSite=Lax';
            window.location.href = pay.payment_page_url; return;
          }
          window.location.href = '/book/done?status=success&kind=booking&appointment=' + appt.id;
        };
        V.api('me/appointments', { body: body }).then(after)
          .catch(function (e) { self.error = e.text(); self.busy = false; });
      },
    };
  };

  /** Result page after a booking / plan purchase / top-up. Re-reads the record so the state shown is the server's. */
  window.bookDone = function (status, kind, id) {
    return {
      status: status, kind: kind, id: id, appt: null, loading: !!id && kind === 'booking',
      init: function () {
        var self = this;
        if (self.loading) V.api('me/appointments/' + id).then(function (r) { self.appt = r.data; if (self.appt.payment_status === 'paid' || self.appt.status === 'confirmed') self.status = 'success'; else if (self.appt.can_pay) self.status = 'pending'; }).catch(function () {}).finally(function () { self.loading = false; });
      },
      title: function () { return this.status === 'success' ? V.t('book.confirmedTitle') : (this.status === 'pending' ? V.t('book.pendingTitle') : V.t('book.failedTitle')); },
      sub: function () { return this.status === 'success' ? V.t('book.confirmedSub') : V.t('book.pendingSub'); },
    };
  };
})();

/* ---------------------------------------------------------------- account */
(function () {
  var V = window.Velto;

  function statusPill(s) {
    return { pending: 'pill--warn', awaiting_payment: 'pill--warn', confirmed: 'pill--ok', assigned: 'pill', on_the_way: 'pill', arrived: 'pill', in_progress: 'pill', completed: 'pill--mint', cancelled: 'pill--gray', no_show: 'pill--danger' }[s] || 'pill--gray';
  }
  function guard(self, load) {
    var auth = Alpine.store('auth');
    var boot = function () { if (!auth.user) { auth.require(); return; } load(); };
    if (auth.ready) boot(); else self.$watch('$store.auth.ready', function (r) { if (r) boot(); });
  }
  /** Start a hosted card payment: remember where to come back, then go to the bank. */
  function goPay(kind, id, url) {
    document.cookie = 'velto_web_pay=' + kind + ':' + (id || 0) + '; path=/; max-age=1800; SameSite=Lax';
    window.location.href = url;
  }

  window.accountBookings = function () {
    return {
      loading: true, items: [], tab: 'upcoming', error: '',
      init: function () { guard(this, this.load.bind(this)); },
      load: function () {
        var self = this; self.loading = true;
        V.api('me/appointments', { query: { filter: 'all' } }).then(function (r) { self.items = r.data || []; }).catch(function (e) { self.error = e.text(); }).finally(function () { self.loading = false; });
      },
      list: function () { var up = this.tab === 'upcoming'; return this.items.filter(function (a) { return !!a.is_upcoming === up; }); },
      pill: statusPill,
      when: function (a) { return a.time_slot ? ('\u2066' + a.time_slot.date + '\u2069 · ' + V.clock(a.time_slot.start_time)) : V.fmtDateTime(a.scheduled_at); },
    };
  };

  window.bookingDetail = function (id) {
    return {
      id: id, a: null, loading: true, error: '', busy: false,
      rescheduling: false, slots: [], days: [], day: null, slot: null,
      rating: 0, comment: '', tracking: null,
      init: function () { guard(this, this.load.bind(this)); },
      load: function () {
        var self = this;
        V.api('me/appointments/' + self.id).then(function (r) { self.a = r.data; if (self.a.review) { self.rating = self.a.review.rating; self.comment = self.a.review.comment || ''; } self.track(); })
          .catch(function (e) { self.error = e.text(); }).finally(function () { self.loading = false; });
      },
      track: function () {
        var self = this; if (!self.a || ['assigned', 'on_the_way', 'arrived', 'in_progress'].indexOf(self.a.status) < 0) return;
        V.api('me/appointments/' + self.id + '/tracking').then(function (r) { self.tracking = r.data; }).catch(function () {});
      },
      pill: statusPill,
      when: function () { var a = this.a; return a.time_slot ? ('\u2066' + a.time_slot.date + '\u2069 · ' + V.clock(a.time_slot.start_time)) : V.fmtDateTime(a.scheduled_at); },
      cancel: function () {
        var self = this; if (!confirm(V.t('acc.cancelConfirm'))) return; self.busy = true;
        V.api('me/appointments/' + self.id + '/cancel', { method: 'POST', body: {} }).then(function (r) { self.a = r.data; Alpine.store('ui').notify(V.t('common.done')); })
          .catch(function (e) { Alpine.store('ui').notify(e.text()); }).finally(function () { self.busy = false; });
      },
      pay: function () {
        var self = this; self.busy = true;
        V.api('me/appointments/' + self.id + '/pay', { method: 'POST', body: {} }).then(function (r) {
          var url = r.data && r.data.payment && r.data.payment.payment_page_url;
          if (url) goPay('booking', self.id, url); else { self.a = r.data.appointment || self.a; self.busy = false; }
        }).catch(function (e) { Alpine.store('ui').notify(e.text()); self.busy = false; });
      },
      startReschedule: function () {
        var self = this; self.rescheduling = true; self.slots = []; self.days = []; self.slot = null;
        V.api('catalog/availability', { auth: false }).then(function (r) {
          self.slots = (r.data || []).map(function (s) { return { id: s.id, date: s.date, start: (s.start_time || '').slice(0, 5), available: !s.is_full }; });
          var seen = {}; self.slots.forEach(function (s) { if (!seen[s.date]) { seen[s.date] = true; self.days.push(s.date); } });
          self.day = self.days[0] || null;
        });
      },
      daySlots: function () { var d = this.day; return this.slots.filter(function (s) { return s.date === d; }); },
      reschedule: function () {
        var self = this; if (!self.slot) return; self.busy = true;
        V.api('me/appointments/' + self.id + '/reschedule', { method: 'PATCH', body: { time_slot_id: self.slot.id } })
          .then(function (r) { self.a = r.data; self.rescheduling = false; Alpine.store('ui').notify(V.t('common.saved')); })
          .catch(function (e) { Alpine.store('ui').notify(e.text()); }).finally(function () { self.busy = false; });
      },
      review: function () {
        var self = this; if (!self.rating) return; self.busy = true;
        V.api('me/appointments/' + self.id + '/review', { body: { rating: self.rating, comment: self.comment || null } })
          .then(function (r) { self.a = r.data; Alpine.store('ui').notify(V.t('common.saved')); })
          .catch(function (e) { Alpine.store('ui').notify(e.text()); }).finally(function () { self.busy = false; });
      },
    };
  };

  window.accountVehicles = function () {
    return {
      loading: true, items: [], brands: [], colors: [], adding: false, editing: null, busy: false, error: '',
      form: { brand: '', model: '', otherBrand: '', otherModel: '', color: '', plate: '', name: '' },
      init: function () { guard(this, this.load.bind(this)); },
      load: function () {
        var self = this;
        Promise.all([V.api('me/vehicles'), V.api('catalog/vehicle-brands', { auth: false }), V.api('catalog/vehicle-colors', { auth: false })])
          .then(function (r) { self.items = r[0].data || []; self.brands = r[1].data || []; self.colors = r[2].data || []; })
          .catch(function (e) { self.error = e.text(); }).finally(function () { self.loading = false; });
      },
      brandModels: function () { var id = this.form.brand; var b = this.brands.find(function (x) { return String(x.id) === String(id); }); return b ? (b.models || []) : []; },
      isOther: function () { return this.form.brand === 'other'; },
      startAdd: function () { this.editing = null; this.form = { brand: '', model: '', otherBrand: '', otherModel: '', color: '', plate: '', name: '' }; this.adding = true; this.error = ''; },
      startEdit: function (v) {
        var b = this.brands.find(function (x) { return x.name === v.brand || x.name_ar === v.brand; });
        var c = this.colors.find(function (x) { return x.name === v.color || x.name_ar === v.color; });
        this.form = { brand: b ? b.id : 'other', model: v.model || '', otherBrand: b ? '' : (v.brand || ''), otherModel: v.model || '', color: c ? c.id : '', plate: v.plate || '', name: v.name || '' };
        this.editing = v; this.adding = true; this.error = '';
      },
      save: function () {
        var self = this; self.error = '';
        var b = self.brands.find(function (x) { return String(x.id) === String(self.form.brand); });
        var brand = self.isOther() ? self.form.otherBrand.trim() : (b ? b.name : '');
        var model = (self.isOther() || !self.brandModels().length) ? self.form.otherModel.trim() : self.form.model;
        if (!brand || !model || !self.form.plate.trim()) { self.error = V.t('common.required'); return; }
        var c = self.colors.find(function (x) { return String(x.id) === String(self.form.color); });
        var body = { name: self.form.name.trim() || null, brand: brand, model: model, color: c ? c.name : null, plate: self.form.plate.trim().toUpperCase() };
        self.busy = true;
        var req = self.editing ? V.api('me/vehicles/' + self.editing.id, { method: 'PATCH', body: body }) : V.api('me/vehicles', { body: body });
        req.then(function () { self.adding = false; self.load(); Alpine.store('ui').notify(V.t('common.saved')); }).catch(function (e) { self.error = e.text(); }).finally(function () { self.busy = false; });
      },
      remove: function (v) {
        var self = this; if (!confirm(V.t('veh.deleteConfirm'))) return;
        V.api('me/vehicles/' + v.id, { method: 'DELETE' }).then(function () { self.load(); }).catch(function (e) { Alpine.store('ui').notify(e.text()); });
      },
      makeDefault: function (v) { var self = this; V.api('me/vehicles/' + v.id + '/default', { method: 'POST', body: {} }).then(function () { self.load(); }).catch(function (e) { Alpine.store('ui').notify(e.text()); }); },
    };
  };

  window.accountWallet = function () {
    return {
      loading: true, w: null, amount: 100, busy: false, error: '',
      init: function () { guard(this, this.load.bind(this)); },
      load: function () { var self = this; V.api('me/wallet').then(function (r) { self.w = r.data; }).catch(function (e) { self.error = e.text(); }).finally(function () { self.loading = false; }); },
      topup: function () {
        var self = this; var amt = Number(self.amount); if (!(amt >= 1)) return; self.busy = true; self.error = '';
        V.api('me/wallet/topup', { body: { amount: amt, payment_method: 'card' } }).then(function (r) {
          var url = r.data && (r.data.payment_page_url || (r.data.payment && r.data.payment.payment_page_url));
          if (url) goPay('wallet', 0, url); else { self.load(); self.busy = false; }
        }).catch(function (e) { self.error = e.text(); self.busy = false; });
      },
      kind: function (k) { var key = 'wal.kind.' + k; var s = V.t(key); return s === key ? k : s; },
    };
  };

  window.accountPlans = function (subscribeId) {
    return {
      loading: true, mine: [], catalog: [], vehicles: [], wallet: null, error: '',
      sel: null, vehicle: null, payment: 'card', busy: false,
      init: function () { guard(this, this.load.bind(this)); },
      load: function () {
        var self = this;
        Promise.all([V.api('me/packages'), V.api('catalog/wash-packages', { auth: false }), V.api('me/vehicles'), V.api('me/wallet')]).then(function (r) {
          self.mine = r[0].data || []; self.catalog = (r[1].data || []).filter(function (p) { return p.type === 'multi'; });
          self.vehicles = r[2].data || []; self.wallet = r[3].data;
          var def = self.vehicles.find(function (v) { return v.is_default; }) || self.vehicles[0]; if (def) self.vehicle = def;
          if (self.wallet && Number(self.wallet.balance) > 0) self.payment = 'wallet';
          if (subscribeId) self.sel = self.catalog.find(function (p) { return p.id === subscribeId; }) || null;
        }).catch(function (e) { self.error = e.text(); }).finally(function () { self.loading = false; });
      },
      statusPill: function (s) { return { active: 'pill--ok', expired: 'pill--gray', exhausted: 'pill--gray', pending_payment: 'pill--warn' }[s] || 'pill--gray'; },
      walletOk: function () { return this.sel && this.wallet && Number(this.wallet.balance) >= Number(this.sel.price); },
      subscribe: function () {
        var self = this; if (!self.sel || !self.vehicle) return;
        if (self.payment === 'wallet' && !self.walletOk()) { self.error = V.t('book.insufficient'); return; }
        self.busy = true; self.error = '';
        V.api('me/packages', { body: { wash_package_id: self.sel.id, vehicle_id: self.vehicle.id, payment_method: self.payment } }).then(function (r) {
          var d = r.data || {}; var url = d.payment && d.payment.payment_page_url;
          if (url) goPay('plan', (d.package || d.customer_package || {}).id, url);
          else { self.sel = null; self.load(); Alpine.store('ui').notify(V.t('common.done')); self.busy = false; }
        }).catch(function (e) { self.error = e.text(); self.busy = false; });
      },
    };
  };

  window.accountProfile = function (cities) {
    return {
      cities: cities || [], areas: [], form: { name: '', email: '', gender: '', city: '', area: '', preferred_language: 'ar' }, busy: false, error: '',
      init: function () {
        var self = this;
        guard(self, function () {
          var u = Alpine.store('auth').user;
          self.form = { name: u.name || '', email: u.email || '', gender: u.gender || '', city: u.city || '', area: u.area || '', preferred_language: u.preferred_language || 'ar' };
          self.$watch('form.city', function () { self.loadAreas(); }); self.loadAreas();
        });
      },
      loadAreas: function () {
        var self = this, c = self.cities.find(function (x) { return x.name === self.form.city || x.name_ar === self.form.city; });
        if (!c) { self.areas = []; return; }
        V.api('locations/cities/' + c.id + '/areas', { auth: false }).then(function (r) { self.areas = r.data || []; }).catch(function () { self.areas = []; });
      },
      save: function () {
        var self = this; self.busy = true; self.error = '';
        var body = { name: self.form.name.trim(), preferred_language: self.form.preferred_language, email: self.form.email.trim() || null, gender: self.form.gender || null, city: self.form.city || null, area: self.form.area || null };
        V.api('auth/profile', { method: 'PATCH', body: body }).then(function (r) { Alpine.store('auth').user = r.data; Alpine.store('ui').notify(V.t('common.saved')); })
          .catch(function (e) { self.error = e.text(); }).finally(function () { self.busy = false; });
      },
      logout: function () { Alpine.store('auth').logout(); },
    };
  };

  window.accountSupport = function (id, mode) {
    return {
      id: id, mode: mode || (id ? 'detail' : 'list'), loading: true, items: [], t: null, error: '', busy: false,
      form: { type: 'complaint', subject: '', message: '', appointment_id: '' }, bookings: [], sent: false,
      init: function () { guard(this, this.load.bind(this)); },
      load: function () {
        var self = this;
        if (self.mode === 'detail') V.api('me/support/tickets/' + self.id).then(function (r) { self.t = r.data; }).catch(function (e) { self.error = e.text(); }).finally(function () { self.loading = false; });
        else if (self.mode === 'new') { V.api('me/appointments', { query: { filter: 'all' } }).then(function (r) { self.bookings = r.data || []; }).catch(function () {}); self.loading = false; }
        else V.api('me/support/tickets').then(function (r) { self.items = r.data || []; }).catch(function (e) { self.error = e.text(); }).finally(function () { self.loading = false; });
      },
      pill: function (s) { return { open: 'pill--warn', in_progress: 'pill', resolved: 'pill--ok', closed: 'pill--gray' }[s] || 'pill--gray'; },
      send: function () {
        var self = this; self.error = '';
        if (!self.form.subject.trim() || !self.form.message.trim()) { self.error = V.t('common.required'); return; }
        self.busy = true;
        var body = { type: self.form.type, subject: self.form.subject.trim(), message: self.form.message.trim() };
        if (self.form.appointment_id) body.appointment_id = Number(self.form.appointment_id);
        V.api('me/support/tickets', { body: body }).then(function (r) { window.location.href = '/account/support/' + r.data.id + '?sent=1'; })
          .catch(function (e) { self.error = e.text(); self.busy = false; });
      },
    };
  };

  window.accountNotifications = function () {
    return {
      loading: true, items: [], error: '',
      init: function () { guard(this, this.load.bind(this)); },
      load: function () { var self = this; V.api('me/notifications').then(function (r) { self.items = (r.data && r.data.items) || []; }).catch(function (e) { self.error = e.text(); }).finally(function () { self.loading = false; }); },
      open: function (n) {
        var self = this; if (!n.is_read) { n.is_read = true; V.api('me/notifications/' + n.id + '/read', { method: 'POST', body: {} }).catch(function () {}); }
        var d = n.data || {};
        if (d.ticket_id) window.location.href = '/account/support/' + d.ticket_id;
        else if (d.appointment_id) window.location.href = '/account/bookings/' + d.appointment_id;
      },
      readAll: function () { var self = this; self.items.forEach(function (n) { n.is_read = true; }); V.api('me/notifications/read-all', { method: 'POST', body: {} }).catch(function () {}); },
      title: function (n) { var l = Alpine.store('ui').lang; return (l === 'ar' ? (n.title_ar || n.title) : (n.title || n.title_ar)) || ''; },
      body: function (n) { var l = Alpine.store('ui').lang; return (l === 'ar' ? (n.body_ar || n.body) : (n.body || n.body_ar)) || ''; },
    };
  };
})();
