<?php

namespace App\Support;

final class Geo
{
    private const EARTH_KM = 6371.0;

    /** Great-circle distance between two lat/lng points, in kilometres. */
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_KM * 2 * asin(min(1.0, sqrt($a)));
    }
}
