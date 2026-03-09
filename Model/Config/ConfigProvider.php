<?php
declare(strict_types=1);

namespace Gtstudio\AiConnector\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    private const XML_PATH_ENABLED = 'aiconnector/general/enabled';
    private const XML_PATH_PROVIDER = 'aiconnector/general/privider';
    private const XML_PATH_DOMAIN = 'aiconnector/general/domain';
    private const XML_PATH_KEY          = 'aiconnector/general/key';
    private const XML_PATH_MODEL        = 'aiconnector/general/model';
    private const XML_PATH_MAX_TOKENS   = 'aiconnector/general/max_tokens';
    private const XML_PATH_TEMPERATURE  = 'aiconnector/general/temperature';
    private const XML_PATH_RULES        = 'aiconnector/general/rules';

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->get(self::XML_PATH_ENABLED) === '1';
    }

    public function getProvider(): string
    {
        return (string)$this->get(self::XML_PATH_PROVIDER);
    }

    public function getDomain(): string
    {
        return (string)$this->get(self::XML_PATH_DOMAIN);
    }

    public function getApiKey(): string
    {
        $encryptedValue =  $this->scopeConfig->getValue(
            self::XML_PATH_KEY,
            ScopeInterface::SCOPE_STORE
        );

        if (!empty($encryptedValue)) {
            return $this->encryptor->decrypt($encryptedValue);
        }

        return '';
    }

    public function getModel(): string
    {
        return (string)$this->get(self::XML_PATH_MODEL);
    }

    public function getMaxTokens(): int
    {
        return (int)$this->get(self::XML_PATH_MAX_TOKENS);
    }

    public function getTemperature(): float
    {
        return (float)$this->get(self::XML_PATH_TEMPERATURE);
    }

    public function getRules(): string
    {
        return (string)$this->get(self::XML_PATH_RULES);
    }

    private function get(string $path): mixed
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
