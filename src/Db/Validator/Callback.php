<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Validator;

class Callback extends \Zend\Validator\Callback
{
    /**
     * Set validation message.
     *
     * {@inheritdoc}
     */
    protected $messageTemplates = [
        self::INVALID_VALUE    => "This is required field if 'url' is not set.",
        self::INVALID_CALLBACK => "An exception has been raised within the callback",
    ];
}
