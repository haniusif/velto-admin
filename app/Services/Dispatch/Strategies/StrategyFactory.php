<?php

namespace App\Services\Dispatch\Strategies;

/**
 * Resolves a strategy key to its implementation via the container (so strategies
 * with dependencies — auto/nearest need scoring + settings — are injected).
 * Strategies not yet built (skill/zone/preferred/emergency) degrade gracefully
 * to auto scoring so a config value never breaks dispatch.
 */
class StrategyFactory
{
    public function make(string $key): AssignmentStrategy
    {
        return app(match ($key) {
            AssignmentStrategy::MANUAL => ManualStrategy::class,
            AssignmentStrategy::ROUND_ROBIN => RoundRobinStrategy::class,
            AssignmentStrategy::CITY_BASED => CityBasedStrategy::class,
            AssignmentStrategy::LEAST_LOADED => LeastLoadedStrategy::class,
            AssignmentStrategy::NEAREST => NearestStrategy::class,
            // auto + everything not yet implemented use weighted scoring
            default => AutoStrategy::class,
        });
    }
}
