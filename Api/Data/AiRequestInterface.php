<?php

namespace Gtstudio\AiConnector\Api\Data;

interface AiRequestInterface
{
    /**
     * String constants for property names
     */
    public const PROMPT = "prompt";
    public const MODEL = "model";
    public const MAX_TOKENS = "max_tokens";
    public const TEMPERATURE = "temperature";
    public const RULES = "rules";

    /**
     * Getter for Prompt.
     *
     * @return string|null
     */
    public function getPrompt(): ?string;

    /**
     * Setter for Prompt.
     *
     * @param string|null $prompt
     *
     * @return void
     */
    public function setPrompt(?string $prompt): void;

    /**
     * Getter for Model.
     *
     * @return string|null
     */
    public function getModel(): ?string;

    /**
     * Setter for Model.
     *
     * @param string|null $model
     *
     * @return void
     */
    public function setModel(?string $model): void;

    /**
     * Getter for MaxTokens.
     *
     * @return int|null
     */
    public function getMaxTokens(): ?int;

    /**
     * Setter for MaxTokens.
     *
     * @param int|null $maxTokens
     *
     * @return void
     */
    public function setMaxTokens(?int $maxTokens): void;

    /**
     * Getter for Temperature.
     *
     * @return float|null
     */
    public function getTemperature(): ?float;

    /**
     * Setter for Temperature.
     *
     * @param float|null $temperature
     *
     * @return void
     */
    public function setTemperature(?float $temperature): void;

    /**
     * Getter for Rules.
     *
     * @return string|null
     */
    public function getRules(): ?string;

    /**
     * Setter for Rules.
     *
     * @param string|null $rules
     *
     * @return void
     */
    public function setRules(?string $rules): void;
}
