<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Validation\Exception;

use SlayerBirden\DataFlow\Exception\DomainExceptionInterface;

final class ValidationException extends \Exception implements DomainExceptionInterface
{
}
