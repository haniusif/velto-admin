<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SliderResource;
use App\Http\Resources\Api\V1\WashPackageResource;
use App\Models\Slider;
use App\Models\WashPackage;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function index(): JsonResponse
    {
        $sliders = Slider::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $services = WashPackage::query()
            ->where('is_active', true)
            ->where('type', 'single')
            ->orderBy('sort_order')
            ->limit(2)
            ->get();

        $featured = WashPackage::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_featured', true)->orWhere('type', 'multi');
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->first();

        return response()->json([
            'data' => [
                'sliders' => SliderResource::collection($sliders),
                'services' => WashPackageResource::collection($services),
                'featured_package' => $featured ? new WashPackageResource($featured) : null,
            ],
        ]);
    }
}
