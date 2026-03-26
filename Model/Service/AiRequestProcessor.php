<?php

declare(strict_types=1);

namespace Gtstudio\AiConnector\Model\Service;

use Gtstudio\AiConnector\Api\Data\AiRequestInterface;
use Gtstudio\AiConnector\Api\Data\AiRequestInterfaceFactory;
use Gtstudio\AiConnector\Model\Client\NeuronClient;
use Gtstudio\AiConnector\Model\Config\ConfigProvider;
use Gtstudio\AiConnector\Model\Exception\AiConnectorException;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Throwable;

class AiRequestProcessor
{
    /**
     * @param NeuronClient $client
     * @param ConfigProvider $config
     * @param StoreManagerInterface $storeManager
     * @param AiRequestInterfaceFactory $aiRequestInterfaceFactory
     * @param State $appState
     * @param CustomerSession $customerSession
     */
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
     * Process an input string through the AI provider and return the response.
     *
     * @param string $input
     * @return string
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
            $previous = $e instanceof \Exception ? $e : new \Exception($e->getMessage(), $e->getCode());

            throw new AiConnectorException(
                __('AI request processing failed.'),
                [
                    'input'       => $input,
                    'store_id'    => $this->getStoreId(),
                    'area_code'   => $this->getAreaCode(),
                    'customer_id' => $this->getCustomerId(),
                    'model'       => $this->config->getModel(),
                    'provider'    => $this->config->getProvider(),
                ],
                $previous
            );
        }
    }

    /**
     * Build an AiRequestInterface from the input string and current config.
     *
     * @param string|null $input
     * @return AiRequestInterface
     * @throws NoSuchEntityException
     */
    private function buildRequest(?string $input): AiRequestInterface
    {
        return $this->aiRequestInterfaceFactory->create([
            'data' => [
                'prompt'      => $this->injectContext($input),
                'model'       => $this->config->getModel(),
                'max_tokens'  => $this->config->getMaxTokens(),
                'temperature' => $this->config->getTemperature(),
                'rules'       => $this->config->getRules(),
            ],
        ]);
    }

    /**
     * Prepend store/area/customer context to the raw input string.
     *
     * @param string $input
     * @return string
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
     * Validate that the connector is properly configured and the input is non-empty.
     *
     * @param string $input
     * @return void
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
     * Get the current store ID.
     *
     * @return int
     * @throws NoSuchEntityException
     */
    private function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    /**
     * Get the current Magento area code.
     *
     * @return string
     */
    private function getAreaCode(): string
    {
        try {
            return $this->appState->getAreaCode();
        } catch (Throwable) {
            return 'unknown';
        }
    }

    /**
     * Get the logged-in customer ID, or null for guests.
     *
     * @return int|null
     */
    private function getCustomerId(): ?int
    {
        if ($this->customerSession->isLoggedIn()) {
            return (int) $this->customerSession->getCustomerId();
        }

        return null;
    }
}
