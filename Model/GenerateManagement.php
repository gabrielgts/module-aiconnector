<?php
/**
 * Copyright © GTstudio All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\AiConnector\Model;

use Gtstudio\AiConnector\Api\GenerateManagementInterface;
use Gtstudio\AiConnector\Model\Exception\AiConnectorException;
use Gtstudio\AiConnector\Model\Service\AiRequestProcessor;
use Magento\Framework\Exception\NoSuchEntityException;

class GenerateManagement implements GenerateManagementInterface
{
    /** @var AiRequestProcessor */
    private AiRequestProcessor $processor;

    /**
     * @param AiRequestProcessor $processor
     */
    public function __construct(
        AiRequestProcessor $processor
    ) {
        $this->processor = $processor;
    }

    /**
     * Generate an AI response for the given prompt.
     *
     * @param string $param
     * @return string
     * @throws NoSuchEntityException
     * @throws AiConnectorException
     */
    public function generate($param)
    {
        return $this->processor->process($param);
    }
}
