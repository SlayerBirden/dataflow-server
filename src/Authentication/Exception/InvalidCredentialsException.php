<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Exception;

use SlayerBirden\DataFlowServer\Exception\DomainExceptionInterface;

class InvalidCredentialsException extends \InvalidArgumentException implements DomainExceptionInterface
{
}
