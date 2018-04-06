<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Exception;

use SlayerBirden\DataFlowServer\Exception\DomainExceptionInterface;

class PasswordRestrictionsException extends \InvalidArgumentException implements DomainExceptionInterface
{
}
