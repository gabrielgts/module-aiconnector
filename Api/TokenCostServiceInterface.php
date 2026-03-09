<?php

declare(strict_types=1);

namespace Gtstudio\AiConnector\Api;

/**
 * Calculate AI token cost for a given model.
 *
 * Implementations can be extended via DI to add or override pricing.
 *
 * @api
 */
interface TokenCostServiceInterface
{
    /**
     * Calculate the estimated cost in USD for a given token count and model.
     *
     * @param int    $tokens Total token count (input + output).
     * @param string $model  Model identifier (e.g. "claude-sonnet-4-6").
     * @return float Cost in USD.
     */
    public function calculateCost(int $tokens, string $model): float;

    /**
     * Return the price per 1 000 tokens for the given model.
     *
     * Returns 0.0 when the model is unknown.
     *
     * @param string $model Model identifier.
     * @return float Price per 1 000 tokens in USD.
     */
    public function getModelPrice(string $model): float;

    /**
     * Return the full pricing table keyed by lowercase model identifier.
     *
     * @return array<string, float>  model => price_per_1000_tokens
     */
    public function getPricingTable(): array;
}
