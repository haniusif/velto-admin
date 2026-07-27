<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SavedAddressResource;
use App\Models\SavedAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedAddressController extends Controller
{
    /** GET /api/v1/me/addresses */
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->savedAddresses()->latest('id')->get();

        return response()->json(['data' => SavedAddressResource::collection($addresses)]);
    }

    /** POST /api/v1/me/addresses */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'is_covered' => ['nullable', 'boolean'],
            'icon_key' => ['nullable', 'string', 'in:home,work,place'],
        ]);

        $address = $request->user()->savedAddresses()->create([
            'label' => $data['label'],
            'subtitle' => $data['subtitle'] ?? null,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'is_covered' => $data['is_covered'] ?? false,
            'icon_key' => $data['icon_key'] ?? 'place',
        ]);

        return response()->json(['data' => new SavedAddressResource($address)], 201);
    }

    /** DELETE /api/v1/me/addresses/{address} */
    public function destroy(Request $request, SavedAddress $address): JsonResponse
    {
        // Scoped to the owner: a stranger's id must 404, not delete.
        abort_unless($address->customer_id === $request->user()?->id, 404);

        $address->delete();

        // 200 with a body, not 204: the app's shared client decodes every
        // response, and an empty one would throw.
        return response()->json(['data' => ['deleted' => true]]);
    }
}
