<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Pipeline\Validation;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

final class PipeValidator extends \Zend\Validator\AbstractValidator
{
    const INVALID = 'invalidPipes';

    /**
     * @var Selectable
     */
    private $pipelineRepository;
    /**
     * @var array Error message templates
     */
    protected $messageTemplates = [
        self::INVALID => "Pipe[s] (%pipes%) do not exist. Please check your input.",
    ];
    /**
     * @var array Error message template variables
     */
    protected $messageVariables = [
        'pipes' => 'pipes'
    ];
    /**
     * @var array
     */
    protected $pipes;

    public function __construct(Selectable $pipelineRepository, $options = null)
    {
        parent::__construct($options);
        $this->pipelineRepository = $pipelineRepository;
    }

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        if (empty($value)) {
            return true;
        }
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->in('id', $value));

        $results = $this->pipelineRepository->matching($criteria);

        if ($results->count() !== count($value)) {
            $existing = [];
            foreach ($results as $result) {
                $existing[] = $result->getId();
            }
            $this->pipes = implode(',', array_diff($value, $existing));
            $this->error(self::INVALID);
            return false;
        }

        return true;
    }
}
