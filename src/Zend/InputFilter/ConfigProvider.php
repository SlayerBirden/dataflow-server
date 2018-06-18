<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Zend\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke()
    {
        return [
            'input_filters' => [
                'aliases' => [
                    'InputFilter' => ImprovedInputFilter::class,
                    'inputFilter' => ImprovedInputFilter::class,
                    'inputfilter' => ImprovedInputFilter::class,
                    InputFilter::class => ImprovedInputFilter::class,
                ],
                'factories' => [
                    ImprovedInputFilter::class => InvokableFactory::class,
                ],
            ],
        ];
    }
}
