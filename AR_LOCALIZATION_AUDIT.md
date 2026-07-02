# Velto Admin — Arabic Localization Audit & Fixes

**Date:** 2026-07-03
**Scope:** velto-admin (Laravel 12 + Filament v4 admin panel). Arabic UI is served via Laravel JSON translations (`__('English')` → `lang/ar.json`) plus `lang/ar/*.php` framework files.

---

## Summary

The admin was already ~95% localized — nearly every Filament label/heading/option uses `__()`. The audit found and fixed: **54 UI strings that fell back to English**, **4 files of framework messages (validation/auth/passwords/pagination) entirely missing in Arabic**, a handful of **hardcoded English labels**, one **mistranslation**, and several **consistency issues**.

| Surface | Before | After |
|---|---|---|
| `lang/ar.json` (Filament UI) | 198 keys, 54 used keys missing → English | 267 keys, **0 missing** |
| `lang/ar/validation.php` | absent → English | **created** (all rules, friendly UX phrasing) |
| `lang/ar/auth.php`, `passwords.php`, `pagination.php` | absent → English | **created** |
| Hardcoded English labels | 3 files | wrapped in `__()` + translated |
| Infolist auto-generated English labels | 2 "View" pages | every entry now `->label(__())` |

Verified live: all 276 `__()` keys used in `app/` now resolve under the `ar` locale; JSON valid; all PHP lints clean.

---

## 1. Missing translations (added — were showing English)

**54 keys** added to `lang/ar.json`, concentrated in Orders / Payments / Dashboard widgets. Selected examples:

| Key | Arabic |
|---|---|
| Orders / Bookings / Payments / Refunds | الطلبات / الحجوزات / المدفوعات / الاستردادات |
| Pending / Paid / Confirmed / Scheduled / Captured / Failed / Cancelled / Refunded | قيد الانتظار / مدفوع / مؤكَّد / مجدوَل / تم التحصيل / فشل / ملغي / تم الاسترداد |
| Payment method / Payment status / Gateway / Card / Apple Pay | طريقة الدفع / حالة الدفع / بوابة الدفع / بطاقة / Apple Pay |
| Base price / Add-ons total / Total | السعر الأساسي / إجمالي الإضافات / الإجمالي |
| Revenue this month / Orders this month / Top-ups this month | إيرادات هذا الشهر / طلبات هذا الشهر / الشحن هذا الشهر |
| Wallet activity — last 14 days / Slot capacity vs bookings / Customers by city | نشاط المحفظة — آخر 14 يومًا / سعة المواعيد مقابل الحجوزات / العملاء حسب المدينة |
| Track ID / Transaction ID / Ref (RRN) | معرّف التتبع / معرّف المعاملة / المرجع (RRN) |
| Assigned worker / Active workers / Unread notifications | الموظف المكلَّف / الموظفون النشطون / الإشعارات غير المقروءة |

(Full list of 54 in git diff of `lang/ar.json`.)

## 2. Framework messages — created from scratch (biggest structural gap)

`lang/ar/` did not exist, so **all** validation errors, login failures, password-reset and pagination text rendered in **English** under the `ar` locale (fallback is `en`). Created:

- **`lang/ar/validation.php`** — every rule, in natural Saudi-app phrasing per the UX brief (action-oriented, not literal):
  - `required` → **«يرجى إدخال :attribute.»** (not "حقل :attribute مطلوب")
  - `email` → **«أدخل بريدًا إلكترونيًا صحيحًا في :attribute.»**
  - `unique` → **«:attribute مستخدم من قبل.»**
  - Includes an `attributes` map (name→الاسم, email→البريد الإلكتروني, phone→رقم الجوال, …) so messages read naturally.
  - All placeholders preserved: `:attribute :value :min :max :date :other :values :format` etc.
- **`lang/ar/auth.php`** — `failed`/`password`/`throttle` (e.g. «بيانات الدخول غير صحيحة.»).
- **`lang/ar/passwords.php`** — reset/sent/throttled/token/user.
- **`lang/ar/pagination.php`** — previous/next.

## 3. Hardcoded English text (wrapped in `__()`)

- **`AppSettingForm.php`** — the field-type `Select` options were raw English (`'String'`, `'Text (multiline)'`, `'URL'`, `'Email'`, `'Telephone'`, `'Boolean'`, `'Number'`) → now `__()`-wrapped + translated (نص / نص متعدد الأسطر / رابط / البريد الإلكتروني / هاتف / منطقي (صح/خطأ) / رقم).
- **`AppointmentInfolist.php`** — 7 hardcoded `->label('...')` (Customer, Vehicle, Wash package, Time slot, Wallet transaction, Area, Zone) **plus** ~18 entries that had *no* label and were auto-humanized to English (Scheduled at, Base price, Payment method, Total, Cancelled at, …). Every entry now `->label(__())`.
- **`PaymentTransactionInfolist.php`** — 2 hardcoded labels (Customer, Appointment) plus all auto-English entries (Gateway, Action, Currency, Track ID, Transaction ID, Ref (RRN), Result code, Error code, Error text, …). Every entry now `->label(__())`.

## 4. Fixed / improved existing Arabic

| Key | Before | After | Why |
|---|---|---|---|
| Single | فرد | **زيارة واحدة** | **Mistranslation** — "فرد" (person) in a plan-type badge; now parallels "متعدد الزيارات". |
| Active / Inactive | مفعّل / غير مفعّل | **نشط / غير نشط** | Align to the agreed status terminology. |
| Verified | متحقّق | **موثّق** | "متحقّق" is awkward; "موثّق" is the standard status word. |
| Joined | انضم (verb) | **تاريخ الانضمام** | It labels a date column, not an action. |
| "…parallel bookings…" | الحجوزات المتوازية | **الحجوزات المتزامنة** | "متوازية" = geometric parallel; "متزامنة" = simultaneous (the intended meaning). |
| (AR)/(Arabic)/(English) suffixes | (عربي) / (إنجليزي) | **(بالعربية) / (بالإنجليزية)** | Consistent, more natural phrasing across Answer/Body/Question/Title/Name/Description. |

## 5. Duplicate translations & keys

- **No duplicate keys** (JSON can't hold them; verified).
- **12 Arabic values are shared by >1 English key** — all intentional synonyms, kept on purpose:
  - `Lang` = `Language` → اللغة; `Dial` = `Dial code` → رمز الاتصال
  - `Single` = `Single visit` → زيارة واحدة; `Joined` = `Joined at` → تاريخ الانضمام; `Total` = `total` → الإجمالي
  - The `(AR)` / `(Arabic)` pairs (e.g. `Answer (AR)` = `Answer (Arabic)` → الإجابة (بالعربية)) — redundant English *keys* come from different Filament screens; harmless. **Recommendation:** collapse to one variant per concept in code when convenient.

## 6. Terminology — consistency review (whole dashboard)

Standardized set now applied everywhere:

| EN | AR | EN | AR |
|---|---|---|---|
| Customer(s) | العميل / العملاء | Vehicle(s) | **السيارة / السيارات** † |
| Service | الخدمة | Wash package | باقة غسيل |
| Appointment | الموعد | Booking(s) | حجز / الحجوزات |
| Time slot(s) | موعد / المواعيد | Worker(s) | موظف / الموظفون |
| Wallet | المحفظة | Payment(s) | الدفعة / المدفوعات |
| Notification(s) | إشعار / الإشعارات | Settings | الإعدادات |
| Zone(s) / Area(s) | منطقة/المناطق / الحي/الأحياء | Status | الحالة |

† **Decision:** Vehicle kept as **سيارة** (not المركبة from the supplied table) — per your call, it's friendlier and matches the existing app.

**One terminology collision flagged (not auto-changed):** the key `Order` → **الترتيب** (sort order) coexists with `Orders` → **الطلبات** (customer orders). Different keys, so both render correctly today, but if any new code calls `__('Order')` meaning "an order (طلب)" it would show "الترتيب". Recommend renaming the sort-order usages to `__('Sort order')` (already = ترتيب العرض) to free the word.

## 7. UX wording improvements applied

Followed the action-oriented, short-label rules:
- Field-type options are terse nouns (نص، رقم، رابط) rather than sentences.
- Validation uses «يرجى إدخال…» / «أدخل… صحيحًا» rather than robotic «حقل … مطلوب».
- Status badges are single words (نشط، ملغي، مؤكَّد، مدفوع).

---

## Out of scope / flagged, not changed

1. **Mobile push-notification strings** hardcoded in controllers (`AppointmentController`, `PaymentController`, `WorkerJobController`, `Appointment` model) are **bilingual data payloads** sent to the customer/worker Flutter apps (e.g. `title_ar => 'تم تأكيد الحجز'`), not admin-dashboard chrome. Arabic quality there is good and on-brand ("فيلتو"). Left as-is; if you want these centralized, they'd move to `lang/ar/notifications.php`.
2. **`resources/views/welcome.blade.php`** (marketing landing) is bilingual via a custom `data-ar`/`data-en` + JS toggle — a parallel system disconnected from `lang/ar.json`. Balanced (147 pairs each), no Arabic gap, but any copy edit must be done twice by hand.
3. **`LegalPageForm` placeholder** `'terms / privacy / refund'` — left English: these are slug *identifiers* the admin types, not prose.
4. **Modules in the brief not present in this admin** (Coupons, Loyalty Points, Subscriptions, Invoices, dedicated Employees/Branches) — no translation surface exists yet; nothing to translate.
5. **`filament-shield` (roles/permissions)** — Arabic covered by the vendor pack (`lang/vendor/filament-shield/ar/`); not touched.

## Recommendations (config, optional)

- Arabic is **opt-in** (`APP_LOCALE=en`) and the language switcher shows **only on the login page** (`AppServiceProvider`). Consider persisting the admin's locale choice and exposing the switcher inside the panel.
- Consider `php artisan optimize:clear` after deploy if translation/config caching is enabled in production.

---

### Files changed
- `lang/ar.json` (198 → 267 keys)
- `lang/ar/validation.php` *(new)*, `lang/ar/auth.php` *(new)*, `lang/ar/passwords.php` *(new)*, `lang/ar/pagination.php` *(new)*
- `app/Filament/Resources/AppSettings/Schemas/AppSettingForm.php`
- `app/Filament/Resources/Appointments/Schemas/AppointmentInfolist.php`
- `app/Filament/Resources/PaymentTransactions/Schemas/PaymentTransactionInfolist.php`
