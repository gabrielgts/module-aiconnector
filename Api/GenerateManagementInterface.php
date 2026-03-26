<?php
/**
 * Copyright © GTstudio All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Gtstudio\AiConnector\Api;

interface GenerateManagementInterface
{

    /**
     * POST for generate api
     *
     * @param string $param
     * @return string
     */
    public function generate($param);
}
