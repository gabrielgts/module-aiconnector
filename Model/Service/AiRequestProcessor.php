<?php

declare(strict_types=1);

namespace Gtstudio\AiConnector\Model\Service;

use Gtstudio\AiConnector\Model\Client\NeuronClient;
use Gtstudio\AiConnector\Model\Config\ConfigProvider;
use Gtstudio\AiConnector\Api\Data\AiRequestInterfaceFactory;
use Gtstudio\AiConnector\Api\Data\AiRequestInterface;
use Gtstudio\AiConnector\Model\Exception\AiConnectorException;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Throwable;

class AiRequestProcessor
{
    public function __construct(
        private NeuronClient $client,
        private ConfigProvider $config,
        private StoreManagerInterface $storeManager,
        private AiRequestInterfaceFactory $aiRequestInterfaceFactory,
        private State $appState,
        private CustomerSession $customerSession
    ) {
    }

    /**
     * @throws NoSuchEntityException
     * @throws AiConnectorException
     */
    public function process(string $input): string
    {
        $this->validate($input);

        try {
            $request = $this->buildRequest($input);

            $response = $this->client->send($request);

            return $response->getContent();

        } catch (Throwable $e) {
            // LocalizedException only accepts \Exception as $previous; wrap
            // non-Exception throwables (e.g. Error) so the chain is preserved.
            $previous = $e instanceof \Exception ? $e : new \Exception($e->getMessage(), $e->getCode());

            throw new AiConnectorException(
                __('AI request processing failed.'),
                [
                    'input'        => $input,
                    'store_id'     => $this->getStoreId(),
                    'area_code'    => $this->getAreaCode(),
                    'customer_id'  => $this->getCustomerId(),
                    'model'        => $this->config->getModel(),
                    'provider'     => $this->config->getProvider(),
                ],
                $previous
            );
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    private function buildRequest(?string $input): AiRequestInterface
    {
        return $this->aiRequestInterfaceFactory->create([
            'prompt' => $this->injectContext($input),
            'model' => $this->config->getModel(),
            'max_tokens' => $this->config->getMaxTokens(),
            'temperature' => $this->config->getTemperature(),
            'rules' => $this->config->getRules()
        ]);
    }

    /**
     * @throws NoSuchEntityException
     */
    private function injectContext(string $input): string
    {
        $context = [
            'Store ID: ' . $this->getStoreId(),
            'Area: ' . $this->getAreaCode(),
            'Customer ID: ' . ($this->getCustomerId() ?? 'guest'),
        ];

        return implode(' | ', $context) . PHP_EOL . PHP_EOL . $input;
    }

    /**
     * @throws AiConnectorException
     */
    private function validate(string $input): void
    {
        if (!$this->config->isEnabled()) {
            throw new AiConnectorException(
                __('AI Connector is disabled.')
            );
        }

        if (trim($input) === '') {
            throw new AiConnectorException(
                __('AI prompt cannot be empty.')
            );
        }

        if (!$this->config->getModel()) {
            throw new AiConnectorException(
                __('AI model is not configured.')
            );
        }

        if (!$this->config->getProvider()) {
            throw new AiConnectorException(
                __('AI provider is not configured.')
            );
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    private function getAreaCode(): string
    {
        try {
            return $this->appState->getAreaCode();
        } catch (Throwable) {
            return 'unknown';
        }
    }

    private function getCustomerId(): ?int
    {
        if ($this->customerSession->isLoggedIn()) {
            return (int)$this->customerSession->getCustomerId();
        }

        return null;
    }
}
