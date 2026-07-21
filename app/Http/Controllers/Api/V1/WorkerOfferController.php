<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WorkerJobResource;
use App\Models\AssignmentOffer;
use App\Services\Dispatch\DispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerOfferController extends Controller
{
    public function __construct(private readonly DispatchService $dispatch) {}

    /** GET /api/v1/worker/offers — pending offers with live countdown. */
    public function index(Request $request): JsonResponse
    {
        $offers = $request->user()->offers()
            ->pending()
            ->where('expires_at', '>', now())
            ->with(['appointment' => fn ($q) => $q->with(['washPackage', 'vehicle', 'timeSlot', 'area', 'zone', 'customer'])])
            ->latest('offered_at')
            ->get();

        return response()->json([
            'data' => $offers->map(fn (AssignmentOffer $o) => $this->present($o))->all(),
        ]);
    }

    /** POST /api/v1/worker/offers/{offer}/accept */
    public function accept(Request $request, AssignmentOffer $offer): JsonResponse
    {
        $this->authorizeOffer($request, $offer);

        $appointment = $this->dispatch->acceptOffer($offer);
        $appointment->load(['washPackage', 'vehicle', 'timeSlot', 'area', 'zone', 'customer']);

        return response()->json(['data' => new WorkerJobResource($appointment)]);
    }

    /** POST /api/v1/worker/offers/{offer}/reject */
    public function reject(Request $request, AssignmentOffer $offer): JsonResponse
    {
        $this->authorizeOffer($request, $offer);

        $this->dispatch->rejectOffer($offer);

        return response()->json(['data' => ['status' => 'rejected']]);
    }

    private function authorizeOffer(Request $request, AssignmentOffer $offer): void
    {
        abort_unless($offer->worker_id === $request->user()->id, 404);
    }

    private function present(AssignmentOffer $offer): array
    {
        return [
            'offer_id' => $offer->id,
            'expires_at' => $offer->expires_at?->toIso8601String(),
            'seconds_left' => max(0, (int) now()->diffInSeconds($offer->expires_at, false)),
            'job' => new WorkerJobResource($offer->appointment),
        ];
    }
}
