<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Zend\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

class ImprovedInputFilterPluginManager extends InputFilterPluginManager
{
    public function __construct($configOrContainer = null, array $v3config = [])
    {
        // Assign improved filter.
        $this->setAlias(InputFilter::class, ImprovedInputFilter::class);
        $this->setFactory(ImprovedInputFilter::class, InvokableFactory::class);
        parent::__construct($configOrContainer, $v3config);
    }
}
