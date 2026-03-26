<?php

declare(strict_types=1);

namespace Gtstudio\AiConnector\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    private const XML_PATH_ENABLED     = 'aiconnector/general/enabled';
    private const XML_PATH_PROVIDER    = 'aiconnector/general/privider';
    private const XML_PATH_DOMAIN      = 'aiconnector/general/domain';
    private const XML_PATH_KEY         = 'aiconnector/general/key';
    private const XML_PATH_MODEL       = 'aiconnector/general/model';
    private const XML_PATH_MAX_TOKENS  = 'aiconnector/general/max_tokens';
    private const XML_PATH_TEMPERATURE = 'aiconnector/general/temperature';
    private const XML_PATH_RULES       = 'aiconnector/general/rules';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private EncryptorInterface $encryptor
    ) {
    }

    /**
     * Check whether the AI Connector is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->get(self::XML_PATH_ENABLED) === '1';
    }

    /**
     * Get the configured AI provider name.
     *
     * @return string
     */
    public function getProvider(): string
    {
        return (string) $this->get(self::XML_PATH_PROVIDER);
    }

    /**
     * Get the base domain for OpenAI-compatible providers.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return (string) $this->get(self::XML_PATH_DOMAIN);
    }

    /**
     * Get the decrypted API key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        $encryptedValue = $this->scopeConfig->getValue(
            self::XML_PATH_KEY,
            ScopeInterface::SCOPE_STORE
        );

        if (!empty($encryptedValue)) {
            return $this->encryptor->decrypt($encryptedValue);
        }

        return '';
    }

    /**
     * Get the configured AI model identifier.
     *
     * @return string
     */
    public function getModel(): string
    {
        return (string) $this->get(self::XML_PATH_MODEL);
    }

    /**
     * Get the maximum number of tokens per response.
     *
     * @return int
     */
    public function getMaxTokens(): int
    {
        return (int) $this->get(self::XML_PATH_MAX_TOKENS);
    }

    /**
     * Get the sampling temperature.
     *
     * @return float
     */
    public function getTemperature(): float
    {
        return (float) $this->get(self::XML_PATH_TEMPERATURE);
    }

    /**
     * Get the system rules / prompt prefix.
     *
     * @return string
     */
    public function getRules(): string
    {
        return (string) $this->get(self::XML_PATH_RULES);
    }

    /**
     * Fetch a store-scoped config value by path.
     *
     * @param string $path
     * @return mixed
     */
    private function get(string $path): mixed
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
