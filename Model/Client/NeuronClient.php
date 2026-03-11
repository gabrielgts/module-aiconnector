<?php

declare(strict_types=1);

namespace Gtstudio\AiConnector\Model\Client;

use Gtstudio\AiConnector\Api\Data\AiRequestInterface;
use Gtstudio\AiConnector\Api\Data\AiResponseInterface;
use Gtstudio\AiConnector\Api\Data\AiResponseInterfaceFactory;
use Gtstudio\AiConnector\Model\Config\ConfigProvider;
use Gtstudio\AiConnector\Model\Exception\AiConnectorException;
use NeuronAI\Chat\Enums\MessageRole;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Deepseek\Deepseek;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\HttpClientOptions;
use NeuronAI\Providers\HuggingFace\HuggingFace;
use NeuronAI\Providers\HuggingFace\InferenceProvider;
use NeuronAI\Providers\Mistral\Mistral;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Providers\OpenAI\Responses\OpenAIResponses;
use NeuronAI\Providers\OpenAILike;
use NeuronAI\Providers\XAI\Grok;

class NeuronClient
{
    // Anthropic requires a fixed API version header; update when Anthropic deprecates this version.
    public const ANTHROPIC_API_VERSION = '2026-02-05';

    public function __construct(
        private readonly ConfigProvider $config,
        private readonly AiResponseInterfaceFactory $aiResponseInterfaceFactory
    ) {
    }

    /**
     * @throws AiConnectorException
     */
    public function send(AiRequestInterface $request): AiResponseInterface
    {
        if (!$this->config->isEnabled()) {
            throw new AiConnectorException(__('AI Connector is disabled.'));
        }

        $provider = $this->resolveProvider($request);

        $rules = $request->getRules() ?: $this->config->getRules();
        if ($rules) {
            $provider->systemPrompt($rules);
        }

        $response = $provider->chat([new Message(MessageRole::USER, $request->getPrompt())]);

        $stopReason = $response->getMetadata('stop_reason');
        if ($stopReason === 'length') {
            throw new AiConnectorException(__(
                'AI response was truncated because it reached the maximum token limit (stop_reason: length). '
                . 'Increase the max_tokens setting or simplify your prompt.'
            ));
        }

        return $this->aiResponseInterfaceFactory->create([
            'data' => [
                'content' => $response->getContent(),
                'raw'     => $response->jsonSerialize(),
            ],
        ]);
    }

    /**
     * @throws AiConnectorException
     */
    public function resolveProvider(?AiRequestInterface $request = null): AIProviderInterface
    {
        $providerName = strtolower($this->config->getProvider());

        // OpenAI uses max_completion_tokens; all other providers use max_tokens.
        $maxTokensKey = $providerName === 'openai' ? 'max_completion_tokens' : 'max_tokens';

        $model      = $request?->getModel()       ?? $this->config->getModel();
        $maxTokens  = $request?->getMaxTokens()   ?? $this->config->getMaxTokens();
        $params     = [
            'temperature' => $request?->getTemperature() ?? $this->config->getTemperature(),
            $maxTokensKey => $maxTokens,
        ];

        switch ($providerName) {
            case 'anthropic':
                return new Anthropic(
                    $this->config->getApiKey(),
                    $model,
                    self::ANTHROPIC_API_VERSION,
                    $maxTokens,
                    $params,
                    new HttpClientOptions(30),
                );

            case 'openai-responses':
                return new OpenAIResponses($this->config->getApiKey(), $model, $params);

            case 'openai':
                return new OpenAI($this->config->getApiKey(), $model, $params);

            case 'openailike':
                return new OpenAILike($this->config->getDomain(), $this->config->getApiKey(), $model, $params);

            case 'ollama':
                return new Ollama($this->config->getDomain(), $model, $params);

            case 'gemini':
                return new Gemini($this->config->getApiKey(), $model, $params);

            case 'mistral':
                return new Mistral($this->config->getApiKey(), $model, $params);

            case 'huggingface':
                return new HuggingFace(
                    $this->config->getApiKey(),
                    $model,
                    InferenceProvider::HF_INFERENCE,
                    false,
                    $params
                );

            case 'deepseek':
                return new Deepseek($this->config->getApiKey(), $model, $params);

            case 'grok':
                return new Grok($this->config->getApiKey(), $model, $params);

            default:
                throw new AiConnectorException(__('Unsupported provider: %1', $providerName));
        }
    }
}
