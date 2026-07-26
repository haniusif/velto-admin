<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CustomerPackageResource;
use App\Models\CustomerPackage;
use App\Models\PaymentTransaction;
use App\Models\WalletTransaction;
use App\Models\WashPackage;
use App\Services\ARB\ArbGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Prepaid multi-visit plans. Buying one mirrors booking: wallet settles inline,
 * card returns a hosted-page URL and the plan only activates on capture.
 */
class CustomerPackageController extends Controller
{
    private const WITH = ['washPackage.addOns', 'vehicle'];

    public function __construct(private readonly ArbGateway $arb) {}

    /** GET /api/v1/me/packages — the customer's plans, newest first. */
    public function index(Request $request): JsonResponse
    {
        $packages = $request->user()->packages()
            ->with(self::WITH)
            ->latest('id')
            ->get();

        return response()->json([
            'data' => CustomerPackageResource::collection($packages),
        ]);
    }

    /** POST /api/v1/me/packages — buy a plan for one vehicle. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'wash_package_id' => ['required', 'integer'],
            'vehicle_id' => ['required', 'integer'],
            'payment_method' => ['required', 'string', 'in:wallet,card,apple_pay'],
        ]);

        $customer = $request->user();

        $vehicle = $customer->vehicles()->find($data['vehicle_id']);
        if (! $vehicle) {
            throw ValidationException::withMessages(['vehicle_id' => ['Vehicle not found.']]);
        }

        $package = WashPackage::where('is_active', true)->find($data['wash_package_id']);
        if (! $package || $package->type !== 'multi' || (int) $package->visits_count < 1) {
            throw ValidationException::withMessages([
                'wash_package_id' => ['This is not a multi-visit plan.'],
            ]);
        }

        return $data['payment_method'] === 'wallet'
            ? $this->buyWithWallet($customer, $package, $vehicle, $data)
            : $this->buyWithCard($request, $customer, $package, $vehicle, $data);
    }

    private function buyWithWallet($customer, WashPackage $package, $vehicle, array $data): JsonResponse
    {
        $price = (float) $package->price;

        if ((float) $customer->wallet_balance < $price) {
            throw ValidationException::withMessages([
                'payment_method' => ['Insufficient wallet balance.'],
            ]);
        }

        $plan = DB::transaction(function () use ($customer, $package, $vehicle, $price) {
            $plan = $customer->packages()->create([
                'wash_package_id' => $package->id,
                'vehicle_id' => $vehicle->id,
                'visits_total' => (int) $package->visits_count,
                'visits_used' => 0,
                'price_paid' => $price,
                'payment_method' => 'wallet',
                'payment_status' => 'pending',
                'status' => CustomerPackage::STATUS_PENDING,
            ]);

            $customer->walletTransactions()->create([
                'kind' => WalletTransaction::KIND_BOOKING,
                'amount' => -$price,
                'note' => "Plan #{$plan->id} — {$package->name}",
            ]);

            $plan->setRelation('washPackage', $package);
            $plan->activate();

            return $plan;
        });

        $plan->load(self::WITH);

        return response()->json([
            'data' => [
                'package' => new CustomerPackageResource($plan),
                'payment' => ['method' => 'wallet', 'status' => 'paid', 'payment_page_url' => null],
            ],
        ], 201);
    }

    private function buyWithCard(Request $request, $customer, WashPackage $package, $vehicle, array $data): JsonResponse
    {
        if (! $this->arb->isConfigured()) {
            throw ValidationException::withMessages([
                'payment_method' => ['Card payment is not available yet. Please pay with your wallet.'],
            ]);
        }

        $price = (float) $package->price;

        // Created pending: no visits are spendable until the payment captures.
        $plan = $customer->packages()->create([
            'wash_package_id' => $package->id,
            'vehicle_id' => $vehicle->id,
            'visits_total' => (int) $package->visits_count,
            'visits_used' => 0,
            'price_paid' => $price,
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pending',
            'status' => CustomerPackage::STATUS_PENDING,
        ]);

        $trackId = 'PK-'.$plan->id.'-'.Str::upper(Str::random(10));

        $payment = PaymentTransaction::create([
            'customer_id' => $customer->id,
            'appointment_id' => null,
            'customer_package_id' => $plan->id,
            'gateway' => 'arb',
            'purpose' => 'package_purchase',
            'action' => 'purchase',
            'status' => PaymentTransaction::STATUS_PENDING,
            'amount' => $price,
            'currency' => 'SAR',
            'track_id' => $trackId,
        ]);

        try {
            $token = $this->arb->createPurchaseToken([
                'amount' => $price,
                'track_id' => $trackId,
                'response_url' => $this->callbackUrl('callback'),
                'error_url' => $this->callbackUrl('error'),
                'lang' => $customer->preferred_language,
                'customer_ip' => $request->ip(),
                'udf1' => 'package_purchase',
            ]);
        } catch (\Throwable $e) {
            $payment->update([
                'status' => PaymentTransaction::STATUS_FAILED,
                'error_text' => mb_substr($e->getMessage(), 0, 250),
            ]);
            $plan->update([
                'payment_status' => 'failed',
                'status' => CustomerPackage::STATUS_CANCELLED,
            ]);

            Log::warning('ARB plan token generation failed', [
                'plan' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not start card payment. Please try again.',
            ], 502);
        }

        $payment->update(['payment_id' => $token['payment_id']]);
        $plan->load(self::WITH);

        return response()->json([
            'data' => [
                'package' => new CustomerPackageResource($plan),
                'payment' => [
                    'method' => $data['payment_method'],
                    'status' => 'pending',
                    'payment_page_url' => $token['payment_url'],
                ],
            ],
        ], 201);
    }

    private function callbackUrl(string $type): string
    {
        $base = rtrim((string) config('services.arb.callback_base'), '/');

        return "{$base}/api/v1/payments/arb/{$type}";
    }
}
