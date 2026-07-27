<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Checking a promo code before committing to a booking.
 *
 * Deliberately previews only — it never redeems. The authoritative check runs
 * again when the booking is created, so a code that lapses between the two
 * still fails there rather than being honoured on a stale preview.
 */
class PromoCodeController extends Controller
{
    /** POST /api/v1/me/promo/preview */
    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $customer = $request->user();
        $subtotal = round((float) $data['subtotal'], 2);

        $promo = PromoCode::findByCode($data['code']);

        if (! $promo) {
            return $this->rejected(PromoCode::REASON_NOT_FOUND);
        }

        $reason = $promo->rejectionReason($customer->id, $subtotal);
        if ($reason !== null) {
            return $this->rejected($reason, [
                'min_order_total' => (float) $promo->min_order_total,
            ]);
        }

        $discount = $promo->discountFor($subtotal);

        return response()->json([
            'data' => [
                'valid' => true,
                'code' => mb_strtoupper($promo->code),
                'description' => $promo->description,
                'description_ar' => $promo->description_ar,
                'type' => $promo->type,
                'value' => (float) $promo->value,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'total' => round($subtotal - $discount, 2),
            ],
        ]);
    }

    private function rejected(string $reason, array $extra = []): JsonResponse
    {
        return response()->json([
            'data' => array_merge(['valid' => false, 'reason' => $reason], $extra),
        ]);
    }
}
