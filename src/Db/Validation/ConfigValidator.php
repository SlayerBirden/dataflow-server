<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Validation;

use Zend\Validator\AbstractValidator;

final class ConfigValidator extends AbstractValidator
{
    const INVALID_VALUE = 'invalidValue';

    protected $messageTemplates = [
        self::INVALID_VALUE => "This is required field if 'url' is not set.",
    ];

    /**
     * {@inheritdoc}
     * @var array $context
     */
    public function isValid($value, array $context = [])
    {
        if (empty($value) && empty($context['url'])) {
            $this->error(self::INVALID_VALUE);
            return false;
        }
        return true;
    }
}
