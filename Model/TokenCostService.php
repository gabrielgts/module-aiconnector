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
     * @param array<string, float> $modelPricing
     *   Price per 1 000 tokens, keyed by lowercase model identifier.
     *   Injected via di.xml so that modules can extend pricing without
     *   subclassing this service.
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
