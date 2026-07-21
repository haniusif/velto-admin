<?php

namespace App\Services\Dispatch\Strategies;

/**
 * Resolves a strategy key to its implementation. Strategies not yet built
 * (nearest, skill/zone/preferred/emergency, auto scoring) degrade gracefully
 * to least-loaded for Phase 1 so a config value never breaks dispatch.
 */
class StrategyFactory
{
    public function make(string $key): AssignmentStrategy
    {
        return match ($key) {
            AssignmentStrategy::MANUAL => new ManualStrategy,
            AssignmentStrategy::ROUND_ROBIN => new RoundRobinStrategy,
            AssignmentStrategy::CITY_BASED => new CityBasedStrategy,
            // least_loaded + everything not yet implemented
            default => new LeastLoadedStrategy,
        };
    }
}
