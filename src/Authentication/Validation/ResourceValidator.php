<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Validation;

use SlayerBirden\DataFlowServer\Authorization\ResourceManagerInterface;
use SlayerBirden\DataFlowServer\Authorization\Service\ResourceManager;
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
    private $resource;

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

    public function __construct($options = null, ?ResourceManagerInterface $manager = null)
    {
        parent::__construct($options);
        $this->manager = $manager;
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $all = $this->getAllResources();

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

    private function getAllResources(): array
    {
        if ($this->manager === null) {
            $this->manager = new ResourceManager();
        }

        return $this->manager->getAllResources();
    }
}
