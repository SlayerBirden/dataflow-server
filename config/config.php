<?php
declare(strict_types=1);

use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
    'config_cache_path' => 'data/config-cache.php',
];

$configs = [
    \Zend\Hydrator\ConfigProvider::class,
    \Zend\Expressive\ConfigProvider::class,
    \Zend\Expressive\Router\ConfigProvider::class,
    \Zend\I18n\ConfigProvider::class,
    \Zend\InputFilter\ConfigProvider::class,
    \Zend\Filter\ConfigProvider::class,
    \Zend\Validator\ConfigProvider::class,
    // App config
    \SlayerBirden\DataFlowServer\Pipeline\ConfigProvider::class,
    \SlayerBirden\DataFlowServer\Db\ConfigProvider::class,
    \SlayerBirden\DataFlowServer\Domain\ConfigProvider::class,
    \SlayerBirden\DataFlowServer\Logger\ConfigProvider::class,
    \SlayerBirden\DataFlowServer\Authorization\ConfigProvider::class,
    \SlayerBirden\DataFlowServer\Authentication\ConfigProvider::class,
    // Include cache configuration
    new ArrayProvider($cacheConfig),
    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
    // Load development config if it exists
    new PhpFileProvider('config/development.config.php'),
];

if (getenv('APP_MODE') === 'test') {
    $configs[] = new PhpFileProvider('config/autoload/{{,*.}test}.php');
    $configs[] = new PhpFileProvider('config/test.config.php');
}

$aggregator = new ConfigAggregator($configs, $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
