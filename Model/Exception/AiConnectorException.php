<?php

declare(strict_types=1);

namespace Gtstudio\AiConnector\Model\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class AiConnectorException extends LocalizedException
{
    /** @var array<string, mixed> */
    private array $context;

    /**
     * @param Phrase $phrase
     * @param array $context
     * @param \Exception|null $previous
     */
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

    /**
     * Get the structured context array attached to this exception.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Add a single key-value pair to the context.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function addContext(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * Get the stack trace including any chained previous exception.
     *
     * @return string
     */
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

    /**
     * Return a debug-friendly array with message, code, context, and trace.
     *
     * @return array<string, mixed>
     */
    public function toDebugArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code'    => $this->getCode(),
            'context' => $this->context,
            'trace'   => $this->getTraceAsStringExtended(),
        ];
    }
}
