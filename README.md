# Gtstudio_AiConnector

Core AI provider abstraction for Magento 2. Every other Gtstudio AI module depends on this one to communicate with external AI providers.

## Preview

![AiConnector — provider configuration and on-demand generation](docs/images/aiconnector-preview.gif)

## AI Studio Ecosystem

`Gtstudio_AiConnector` is the foundation of the **AI Studio** suite — a collection of modules that bring AI capabilities to the Magento admin without coupling your store to a single provider. Each module builds on top of this connector and can be installed independently.

| Module | Repository | Description |
|--------|-----------|-------------|
| **Gtstudio_AiConnector** | *(this module)* | Core AI provider abstraction |
| **Gtstudio_AiAgents** | [module-ai-agents](https://github.com/gabrielgts/module-ai-agents) | Agent & tool orchestration, cron scheduling, execution log |
| **Gtstudio_AiWidgets** | [module-ai-widgets](https://github.com/gabrielgts/module-ai-widgets) | Floating admin chat widget + PageBuilder AI generator |
| **Gtstudio_AiDataQuery** | [module-ai-data-query](https://github.com/gabrielgts/module-ai-data-query) | Natural-language store analytics (privacy-first) |
| **Gtstudio_AiKnowledgeBase** | [module-ai-knowledge-base](https://github.com/gabrielgts/module-ai-knowledge-base) | Document upload & RAG retrieval for agents |
| **Gtstudio_AiDashboard** | *(coming soon)* | AI-powered KPI dashboard with ML insights |

### Gtstudio_AiAgents — Agent & Tool Orchestration

Define AI agents directly in the admin UI and attach reusable tools they can call. Each agent has a structured system prompt (background, reasoning steps, output rules) and a list of tools with property schemas that the LLM uses to decide what to execute.

**Key advantages**

- No code required to create a new agent — everything is configured in the admin
- **Run Now** — execute any agent on-demand from its edit page and see the result immediately
- **Cron scheduling** — attach a cron expression to any agent so it runs automatically (e.g. generate a daily sales report every morning)
- **Execution log** — every run is recorded with status, token counts, input, output, and trigger source; old entries are pruned automatically
- **Extensible tool registry** — implement `ToolExecutorInterface` and register it via `di.xml` to give agents access to any custom PHP logic

### Gtstudio_AiWidgets — Admin UI Widgets

Surfaces AI capabilities directly inside the Magento admin without requiring users to leave the page they are working on.

**Key advantages**

- **Floating chat assistant** — a persistent panel available on every admin page, backed by any agent you define (default: `admin_assistant`)
- **PageBuilder AI generator** — an *AI Generate* button inside PageBuilder HTML blocks lets content editors describe what they want and have the AI write clean semantic HTML into the block
- Real-time token and cost estimation on every message
- Fully overridable via standard Magento layout XML and DI preferences — no core patches

### Gtstudio_AiDataQuery — Natural-Language Store Analytics

Lets admin users ask plain-English questions about their store data and receive structured results — without ever sending store data to the LLM.

**Key advantages**

- **Privacy-first two-phase architecture**: the LLM only sees the user's question and returns a structured intent (which tool to call, which parameters); all actual database queries run locally on your server
- Built-in analytics tools cover the most common reporting needs: order analytics, customer lifetime value, product performance, and a general entity query
- Fully extensible: register custom query tools and entity handlers via `di.xml`; override the planner prompt to teach the LLM about your custom tools

### Gtstudio_AiKnowledgeBase — Document Context for Agents

Upload PDF or plain-text documents in the admin and associate them with agents. At query time, relevant excerpts are retrieved and prepended to the agent's context — enabling retrieval-augmented generation (RAG) without a vector database.

**Key advantages**

- No external vector store required — excerpts are stored and matched in the Magento database
- Only the most relevant excerpts are sent to the LLM, keeping token usage low
- Extensible extraction pipeline: add support for new file formats (DOCX, CSV, etc.) by implementing `ExtractorInterface` and registering via `di.xml`
- Swappable retrieval strategy: replace the default keyword matcher with OpenSearch k-NN or any other similarity search by implementing `RetrievalServiceInterface`

---

## What It Does

- Wraps [neuron-core/neuron-ai](https://github.com/inspector-apm/neuron-ai) to expose a single, consistent interface regardless of which AI provider is configured
- Provides a REST endpoint for on-demand AI text generation
- Manages all provider configuration (API key, model, temperature, max tokens) in one place under *Stores → Configuration → AI Connector*
- Ships a `TokenCostService` for tracking and estimating token costs across providers

## Supported Providers

| Key | Provider |
|-----|----------|
| `anthropic` | Anthropic Claude |
| `openai` | OpenAI Chat Completions |
| `openai-responses` | OpenAI Responses API |
| `openailike` | Any OpenAI-compatible endpoint (custom domain) |
| `ollama` | Ollama (local models) |
| `gemini` | Google Gemini |
| `mistral` | Mistral AI |
| `huggingface` | HuggingFace Inference |
| `deepseek` | DeepSeek |
| `grok` | xAI Grok |

## Requirements

- Magento 2.4.4+
- PHP 8.1+
- `neuron-core/neuron-ai ^2`

## Installation

```bash
composer require gtstudio/module-aiconnector
php bin/magento module:enable Gtstudio_AiConnector
php bin/magento setup:upgrade
```

## Configuration

*Stores → Configuration → Gtstudio → AI Connector → General*

| Field | Description |
|-------|-------------|
| Enabled | Master on/off switch |
| Provider | Provider key from the table above |
| Domain | Base URL — required for `openailike` and `ollama` |
| API Key | Provider API key |
| Model | Model identifier (e.g. `claude-sonnet-4-6`, `gpt-4o`) |
| Max Tokens | Maximum tokens in the AI response |
| Temperature | Sampling temperature (0.0–1.0) |
| Rules | Default system-prompt text prepended to every request |

## REST API

### Generate text

```
POST /rest/V1/aiconnector/generate
```

**Request body**

```json
{
  "request": {
    "prompt": "Summarise the top 3 benefits of Magento 2.",
    "rules": "Reply in bullet points.",
    "model": "claude-sonnet-4-6",
    "temperature": 0.7,
    "max_tokens": 512
  }
}
```

All fields except `prompt` are optional and fall back to the admin-configured values.

## Extensibility

### Adding a new provider

Create a plugin on `NeuronClient::resolveProvider()`:

```xml
<!-- etc/di.xml -->
<type name="Gtstudio\AiConnector\Model\Client\NeuronClient">
    <plugin name="vendor_module_custom_provider"
            type="Vendor\Module\Plugin\CustomProviderPlugin"/>
</type>
```

```php
class CustomProviderPlugin
{
    public function aroundResolveProvider(NeuronClient $subject, callable $proceed, $request = null)
    {
        if (strtolower($subject->getConfig()->getProvider()) === 'myprovider') {
            return new MyCustomProvider(...);
        }
        return $proceed($request);
    }
}
```

### Extending the token pricing table

Magento merges `xsi:type="array"` arguments automatically — add or override prices in your module's `di.xml`:

```xml
<type name="Gtstudio\AiConnector\Model\TokenCostService">
    <arguments>
        <argument name="modelPricing" xsi:type="array">
            <!-- New model -->
            <item name="my-custom-model" xsi:type="number">0.002</item>
            <!-- Override existing -->
            <item name="gpt-4o" xsi:type="number">0.004</item>
        </argument>
    </arguments>
</type>
```

### Using NeuronClient in another module

Inject `NeuronClient` and call `resolveProvider()` to get a ready-to-use `AIProviderInterface`:

```php
use Gtstudio\AiConnector\Model\Client\NeuronClient;
use NeuronAI\Chat\Messages\UserMessage;

class MyService
{
    public function __construct(private readonly NeuronClient $client) {}

    public function ask(string $question): string
    {
        $provider = $this->client->resolveProvider();
        $response = $provider->chat(new UserMessage($question));
        return (string) $response->getContent();
    }
}
```

### Injecting a custom system prompt at runtime

Set `rules` on the `AiRequestInterface` object — it overrides the admin-configured default for that request only.
