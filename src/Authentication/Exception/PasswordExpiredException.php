<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Exception;

use SlayerBirden\DataFlowServer\Exception\DomainExceptionInterface;

class PasswordExpiredException extends \LogicException implements DomainExceptionInterface
{
}
