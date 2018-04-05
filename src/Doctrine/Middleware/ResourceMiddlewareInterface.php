<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Middleware;

use Psr\Http\Server\MiddlewareInterface;

interface ResourceMiddlewareInterface extends MiddlewareInterface
{
    /**
     * Key to store data resource in Request object
     */
    const DATA_RESOURCE = 'dataResource';
}
