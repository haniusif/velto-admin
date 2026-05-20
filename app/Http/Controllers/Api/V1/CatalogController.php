<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CatalogCountryResource;
use App\Http\Resources\Api\V1\CatalogFaqResource;
use App\Http\Resources\Api\V1\CatalogLegalPageResource;
use App\Http\Resources\Api\V1\CatalogVehicleBrandResource;
use App\Http\Resources\Api\V1\CatalogVehicleColorResource;
use App\Http\Resources\Api\V1\WashPackageResource;
use App\Models\AppSetting;
use App\Models\Country;
use App\Models\Faq;
use App\Models\LegalPage;
use App\Models\VehicleBrand;
use App\Models\VehicleColor;
use App\Models\WashPackage;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /** GET /api/v1/catalog/vehicle-brands */
    public function vehicleBrands(): JsonResponse
    {
        $brands = VehicleBrand::query()
            ->where('is_active', true)
            ->with(['models' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => CatalogVehicleBrandResource::collection($brands),
        ]);
    }

    /** GET /api/v1/catalog/vehicle-colors */
    public function vehicleColors(): JsonResponse
    {
        $colors = VehicleColor::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => CatalogVehicleColorResource::collection($colors),
        ]);
    }

    /** GET /api/v1/catalog/countries */
    public function countries(): JsonResponse
    {
        $countries = Country::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => CatalogCountryResource::collection($countries),
        ]);
    }

    /** GET /api/v1/catalog/faqs */
    public function faqs(): JsonResponse
    {
        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => CatalogFaqResource::collection($faqs),
        ]);
    }

    /** GET /api/v1/catalog/legal/{slug} */
    public function legal(string $slug): JsonResponse
    {
        $page = LegalPage::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'data' => new CatalogLegalPageResource($page),
        ]);
    }

    /** GET /api/v1/catalog/support */
    public function support(): JsonResponse
    {
        return response()->json([
            'data' => AppSetting::group('support'),
        ]);
    }

    /** GET /api/v1/catalog/wash-packages */
    public function washPackages(): JsonResponse
    {
        $packages = WashPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => WashPackageResource::collection($packages),
        ]);
    }

    /** GET /api/v1/catalog/coverage/check?lat=&lng= → returns matching zone id/name or null */
    public function coverageCheck(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
        ]);

        $point = [(float) $data['lng'], (float) $data['lat']];

        $zones = Zone::query()
            ->where('is_active', true)
            ->whereNotNull('geometry')
            ->with('area:id,name,name_ar,city_id')
            ->get();

        foreach ($zones as $zone) {
            if ($this->pointInGeometry($point, $zone->geometry)) {
                return response()->json([
                    'data' => [
                        'covered' => true,
                        'zone' => [
                            'id' => $zone->id,
                            'name' => $zone->name,
                            'name_ar' => $zone->name_ar,
                            'color' => $zone->color,
                        ],
                        'area' => $zone->area ? [
                            'id' => $zone->area->id,
                            'name' => $zone->area->name,
                            'name_ar' => $zone->area->name_ar,
                        ] : null,
                    ],
                ]);
            }
        }

        return response()->json(['data' => ['covered' => false]]);
    }

    /** Point-in-polygon for Polygon and MultiPolygon GeoJSON. Coords are [lng, lat]. */
    private function pointInGeometry(array $point, ?array $geojson): bool
    {
        if (! $geojson) {
            return false;
        }

        $features = $geojson['features'] ?? [$geojson];

        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? $feature;
            $type = $geometry['type'] ?? null;
            $coords = $geometry['coordinates'] ?? null;
            if (! $coords) {
                continue;
            }

            if ($type === 'Polygon' && $this->pointInPolygon($point, $coords[0] ?? [])) {
                return true;
            }
            if ($type === 'MultiPolygon') {
                foreach ($coords as $polygon) {
                    if ($this->pointInPolygon($point, $polygon[0] ?? [])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /** Ray-casting; ring is [[lng, lat], ...] closed. */
    private function pointInPolygon(array $point, array $ring): bool
    {
        $x = $point[0];
        $y = $point[1];
        $inside = false;
        $n = count($ring);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][0]; $yi = $ring[$i][1];
            $xj = $ring[$j][0]; $yj = $ring[$j][1];
            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-12) + $xi);
            if ($intersect) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
