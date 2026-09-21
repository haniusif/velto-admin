<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\City;
use App\Models\District;
use App\Models\Faq;
use App\Models\LegalPage;
use App\Models\Slider;
use App\Models\WashPackage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * The public website. Marketing content is rendered here from the same tables
 * the app reads (services, plans, FAQ, legal, coverage, contacts), so the two
 * never disagree. Everything interactive — sign-in, booking, the account —
 * is the browser talking to /api/v1 exactly as the app does; these methods
 * only serve the shells.
 */
class SiteController extends Controller
{
    public function __construct()
    {
        // Used by every page's CTAs; shared so child views get it before the layout renders.
        view()->share('arrow', '<svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>');
    }

    public function supportContacts(): array
    {
        return AppSetting::group('support');
    }

    public function home(): View
    {
        return view('site.home', [
            'solidNav' => false,
            'support' => $this->supportContacts(),
            'sliders' => Slider::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'services' => $this->packages('single'),
            'plans' => $this->packages('multi'),
            'faqs' => Faq::query()->where('is_active', true)->orderBy('sort_order')->limit(6)->get(),
            'coverage' => $this->coveredDistricts(),
        ]);
    }

    public function services(): View
    {
        return view('site.services', [
            'title' => 'الخدمات',
            'support' => $this->supportContacts(),
            'services' => $this->packages('single'),
        ]);
    }

    public function plans(): View
    {
        return view('site.plans', [
            'title' => 'الباقات',
            'support' => $this->supportContacts(),
            'plans' => $this->packages('multi'),
        ]);
    }

    public function faq(): View
    {
        return view('site.faq', [
            'title' => 'الأسئلة الشائعة',
            'support' => $this->supportContacts(),
            'faqs' => Faq::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function legal(string $slug): View
    {
        $page = LegalPage::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('site.legal', [
            'title' => $page->title_ar ?: $page->title,
            'support' => $this->supportContacts(),
            'page' => $page,
        ]);
    }

    public function coverage(): View
    {
        return view('site.coverage', [
            'title' => 'مناطق التغطية',
            'support' => $this->supportContacts(),
            'coverage' => $this->coveredDistricts(),
            'mapsKey' => config('services.google_maps.key'),
        ]);
    }

    public function login(Request $request): View
    {
        return view('site.login', [
            'title' => 'تسجيل الدخول',
            'support' => $this->supportContacts(),
            'next' => $this->safeNext($request),
        ]);
    }

    public function completeProfile(Request $request): View
    {
        return view('site.complete-profile', [
            'title' => 'إكمال الملف',
            'support' => $this->supportContacts(),
            'next' => $this->safeNext($request),
            'cities' => City::query()->orderBy('name')->get(['id', 'name', 'name_ar']),
        ]);
    }

    public function book(Request $request): View
    {
        return view('site.book', [
            'title' => 'احجز غسلتك',
            'support' => $this->supportContacts(),
            'mapsKey' => config('services.google_maps.key'),
            'preselect' => [
                'service' => (int) $request->query('service', 0),
                'plan' => (int) $request->query('plan', 0),
            ],
        ]);
    }

    public function bookDone(Request $request): View
    {
        return view('site.book-done', [
            'title' => 'حالة الحجز',
            'support' => $this->supportContacts(),
            'status' => (string) $request->query('status', 'unknown'),
            'appointment' => (int) $request->query('appointment', 0),
            'kind' => (string) $request->query('kind', 'booking'),
        ]);
    }

    /** One shell for every account page; the section decides which component mounts. */
    public function account(string $section = 'bookings', ?string $id = null): View
    {
        abort_unless(in_array($section, ['bookings', 'vehicles', 'wallet', 'plans', 'profile', 'support', 'notifications'], true), 404);

        return view('site.account', [
            'title' => 'حسابي',
            'support' => $this->supportContacts(),
            'section' => $section,
            'id' => $id,
            'mapsKey' => config('services.google_maps.key'),
        ]);
    }

    // ------------------------------------------------------------------

    private function packages(string $type)
    {
        return WashPackage::query()
            ->where('is_active', true)
            ->where('type', $type)
            ->with(['addOns' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function coveredDistricts()
    {
        return District::query()->where('is_covered', true)->orderBy('name')->get(['id', 'name', 'name_ar']);
    }

    /** Only ever bounce back to a path on this site. */
    private function safeNext(Request $request): string
    {
        $next = (string) $request->query('next', '/account');

        return str_starts_with($next, '/') && ! str_starts_with($next, '//') ? $next : '/account';
    }
}
