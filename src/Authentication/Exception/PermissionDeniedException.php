<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Exception;

use SlayerBirden\DataFlowServer\Exception\DomainExceptionInterface;

class PermissionDeniedException extends \LogicException implements DomainExceptionInterface
{
}
