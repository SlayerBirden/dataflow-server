<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Authorization\Entities\History;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use Zend\ServiceManager\Factory\FactoryInterface;

final class PermissionHistoryRepositoryFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var EntityManagerRegistry $manager */
        $manager = $container->get(EntityManagerRegistry::class);

        return $manager->getRepository(History::class);
    }
}
