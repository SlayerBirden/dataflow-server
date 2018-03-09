<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Zend\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputInterface;

class ImprovedInputFilter extends InputFilter
{
    /**
     * Add null values for all the inputs that were not passed.
     * This is needed to make optional fields still validated even when the value is not directly provided in the request.
     *
     * {@inheritdoc}
     */
    public function setData($data)
    {
        foreach ($this->getInputs() as $name => $input) {
            if ($input instanceof InputInterface) {
                if (is_numeric($name)) {
                    $name = $input->getName();
                }
                if (!empty($name) && !array_key_exists($name, $data)) {
                    $data[$name] = null;
                }
            }
        }

        return parent::setData($data);
    }
}
