<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AreaResource;
use App\Http\Resources\Api\V1\CityResource;
use App\Models\Area;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class LocationsController extends Controller
{
    /** GET /api/v1/locations/cities */
    public function cities(): JsonResponse
    {
        $cities = City::query()
            ->where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => CityResource::collection($cities),
        ]);
    }

    /** GET /api/v1/locations/cities/{city}/areas */
    public function areas(City $city): JsonResponse
    {
        $areas = Area::query()
            ->where('city_id', $city->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => AreaResource::collection($areas),
        ]);
    }
}
