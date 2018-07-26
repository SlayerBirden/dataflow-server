<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Validation;

use SlayerBirden\DataFlowServer\Authorization\ResourceManagerInterface;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception\InvalidArgumentException;

class ResourceValidator extends AbstractValidator
{
    const INVALID = 'invalidResource';

    /**
     * @var null|ResourceManagerInterface
     */
    private $manager;
    /**
     * @var string
     */
    protected $resource;

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::INVALID => "Resource '%resource%' is invalid and is not among the list of known resources.",
    ];
    /**
     * @var array Error message template variables
     */
    protected $messageVariables = [
        'resource' => 'resource'
    ];

    public function __construct(ResourceManagerInterface $manager, $options = null)
    {
        parent::__construct($options);
        $this->manager = $manager;
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $all = $this->manager->getAllResources();

        if (!is_array($value)) {
            throw new InvalidArgumentException('Resources param needs to be an array.');
        }

        foreach ($value as $resource) {
            if (!in_array($resource, $all, true)) {
                $this->resource = $resource;
                $this->error(self::INVALID);
            }
        }

        return true;
    }
}
