<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Exception;

use SlayerBirden\DataFlowServer\Exception\DomainExceptionInterface;

class NonExistingEntity extends \InvalidArgumentException implements DomainExceptionInterface
{
}
