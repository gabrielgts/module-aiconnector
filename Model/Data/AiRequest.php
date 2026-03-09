<?php
declare(strict_types=1);

namespace Gtstudio\AiConnector\Model\Data;

use Gtstudio\AiConnector\Api\Data\AiRequestInterface;
use Magento\Framework\DataObject;

class AiRequest extends DataObject implements AiRequestInterface
{
    /**
     * Getter for Prompt.
     *
     * @return string|null
     */
    public function getPrompt(): ?string
    {
        return $this->getData(self::PROMPT);
    }

    /**
     * Setter for Prompt.
     *
     * @param string|null $prompt
     *
     * @return void
     */
    public function setPrompt(?string $prompt): void
    {
        $this->setData(self::PROMPT, $prompt);
    }

    /**
     * Getter for Model.
     *
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->getData(self::MODEL);
    }

    /**
     * Setter for Model.
     *
     * @param string|null $model
     *
     * @return void
     */
    public function setModel(?string $model): void
    {
        $this->setData(self::MODEL, $model);
    }

    /**
     * Getter for MaxTokens.
     *
     * @return int|null
     */
    public function getMaxTokens(): ?int
    {
        return $this->getData(self::MAX_TOKENS) === null ? null
            : (int)$this->getData(self::MAX_TOKENS);
    }

    /**
     * Setter for MaxTokens.
     *
     * @param int|null $maxTokens
     *
     * @return void
     */
    public function setMaxTokens(?int $maxTokens): void
    {
        $this->setData(self::MAX_TOKENS, $maxTokens);
    }

    /**
     * Getter for Temperature.
     *
     * @return float|null
     */
    public function getTemperature(): ?float
    {
        return $this->getData(self::TEMPERATURE) === null ? null
            : (float)$this->getData(self::TEMPERATURE);
    }

    /**
     * Setter for Temperature.
     *
     * @param float|null $temperature
     *
     * @return void
     */
    public function setTemperature(?float $temperature): void
    {
        $this->setData(self::TEMPERATURE, $temperature);
    }

    /**
     * Getter for Rules.
     *
     * @return string|null
     */
    public function getRules(): ?string
    {
        return $this->getData(self::RULES);
    }

    /**
     * Setter for Rules.
     *
     * @param string|null $rules
     *
     * @return void
     */
    public function setRules(?string $rules): void
    {
        $this->setData(self::RULES, $rules);
    }
}
