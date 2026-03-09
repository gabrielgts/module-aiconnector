<?php

namespace Gtstudio\AiConnector\Api\Data;

interface AiResponseInterface
{
    /**
     * String constants for property names
     */
    public const CONTENT = "content";
    public const RAW = "raw";

    /**
     * Getter for Content.
     *
     * @return string|null
     */
    public function getContent(): ?string;

    /**
     * Setter for Content.
     *
     * @param string|null $content
     *
     * @return void
     */
    public function setContent(?string $content): void;

    /**
     * Getter for Raw.
     *
     * @return array|string|null
     */
    public function getRaw(): array|string|null;

    /**
     * Setter for Raw.
     *
     * @param array|string|null $raw
     *
     * @return void
     */
    public function setRaw(array|string|null $raw): void;
}
