<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Extractor;

use Zend\Hydrator\ClassMethods;

class RecursiveEntitiesExtractor extends ClassMethods
{
    /**
     * Add logic to recursively extract nested objects
     *
     * {@inheritdoc}
     */
    public function extractValue($name, $value, $object = null)
    {
        $value = parent::extractValue($name, $value, $object);
        if (is_object($value)) {
            if ($value instanceof \DateTime) {
                $value = $value->format(\DateTime::RFC3339);
            } else {
                $value = parent::extract($value);
            }
        }

        return $value;
    }
}
