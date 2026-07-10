<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vehicles = $request->user()->vehicles()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => VehicleResource::collection($vehicles)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $customer = $request->user();
        $isFirst = ! $customer->vehicles()->exists();

        $vehicle = $customer->vehicles()->create([
            ...$data,
            'is_default' => $isFirst,
        ]);

        return response()->json(['data' => new VehicleResource($vehicle)], 201);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeOwn($request, $vehicle);

        $data = $this->validated($request, isUpdate: true);
        $vehicle->update($data);

        return response()->json(['data' => new VehicleResource($vehicle->fresh())]);
    }

    public function destroy(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeOwn($request, $vehicle);
        $wasDefault = $vehicle->is_default;
        $customerId = $vehicle->customer_id;

        $vehicle->delete();

        if ($wasDefault) {
            // Promote the most recent remaining one as default.
            $next = Vehicle::where('customer_id', $customerId)->latest('id')->first();
            $next?->update(['is_default' => true]);
        }

        return response()->json(['data' => ['ok' => true]]);
    }

    public function setDefault(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeOwn($request, $vehicle);

        DB::transaction(function () use ($request, $vehicle): void {
            $request->user()->vehicles()->update(['is_default' => false]);
            $vehicle->update(['is_default' => true]);
        });

        return response()->json(['data' => new VehicleResource($vehicle->fresh())]);
    }

    /**
     * POST /api/v1/me/vehicles/photo
     *
     * Upload a vehicle photo to the public disk and return its URL. The client
     * then saves the returned url/path as `photo_url` on create/update.
     */
    public function photo(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('photo')->store('vehicle-photos', 'public');

        return response()->json(['data' => [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ]], 201);
    }

    private function validated(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'brand' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'model' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'plate' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:32'],
            'photo_url' => ['nullable', 'string', 'max:1024'],
        ]);
    }

    private function authorizeOwn(Request $request, Vehicle $vehicle): void
    {
        abort_unless($vehicle->customer_id === $request->user()?->id, 404);
    }
}
