<?php

declare(strict_types=1);

namespace Gtstudio\AiConnector\Model\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class AiConnectorException extends LocalizedException
{
    private array $context;

    public function __construct(
        Phrase $phrase,
        array $context = [],
        ?\Exception $previous = null
    ) {
        parent::__construct($phrase, $previous);
        $this->context = $context;
        $this->context['exception_class'] = static::class;
        $this->context['file'] = $this->getFile();
        $this->context['line'] = $this->getLine();
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function addContext(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    public function getTraceAsStringExtended(): string
    {
        $trace = $this->getTraceAsString();

        if ($this->getPrevious()) {
            $trace .= PHP_EOL
                . '--- Previous Exception ---'
                . PHP_EOL
                . $this->getPrevious()->getTraceAsString();
        }

        return $trace;
    }

    public function toDebugArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'context' => $this->context,
            'trace' => $this->getTraceAsStringExtended(),
        ];
    }
}
