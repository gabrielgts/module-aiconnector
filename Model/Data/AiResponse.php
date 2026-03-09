<?php
declare(strict_types=1);

namespace Gtstudio\AiConnector\Model\Data;

use Gtstudio\AiConnector\Api\Data\AiResponseInterface;
use Magento\Framework\DataObject;

class AiResponse extends DataObject implements AiResponseInterface
{
    /**
     * Getter for Content.
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Setter for Content.
     *
     * @param string|null $content
     *
     * @return void
     */
    public function setContent(?string $content): void
    {
        $this->setData(self::CONTENT, $content);
    }

    /**
     * Getter for Raw.
     *
     * @return array|string|null
     */
    public function getRaw(): null|array|string
    {
        return $this->getData(self::RAW);
    }

    /**
     * Setter for Raw.
     *
     * @param array|string|null $raw
     *
     * @return void
     */
    public function setRaw(null|array|string $raw): void
    {
        $this->setData(self::RAW, $raw);
    }
}
