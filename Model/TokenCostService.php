<?php

declare(strict_types=1);

namespace Gtstudio\AiConnector\Model;

use Gtstudio\AiConnector\Api\TokenCostServiceInterface;

/**
 * Default token cost implementation.
 *
 * The pricing table is injected via DI, allowing any module to extend or
 * override individual entries by declaring a <type> argument in di.xml.
 */
class TokenCostService implements TokenCostServiceInterface
{
    /**
     * @param array $modelPricing Price per 1000 tokens keyed by lowercase model identifier; injected via di.xml.
     */
    public function __construct(
        private readonly array $modelPricing = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function calculateCost(int $tokens, string $model): float
    {
        $price = $this->getModelPrice($model);
        return $tokens * $price / 1000;
    }

    /**
     * @inheritDoc
     */
    public function getModelPrice(string $model): float
    {
        return $this->modelPricing[strtolower($model)] ?? 0.0;
    }

    /**
     * @inheritDoc
     */
    public function getPricingTable(): array
    {
        return $this->modelPricing;
    }
}
