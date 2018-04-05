<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Validation;

use Zend\Validator\AbstractValidator;

class ResourceValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        // todo use resource manager to check incoming resources
        return true;
    }
}
