<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Factory;

use Interop\Container\ContainerInterface;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use Zend\ServiceManager\Factory\FactoryInterface;

final class UserRepositoryFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var EntityManagerRegistry $manager */
        $manager = $container->get(EntityManagerRegistry::class);

        return $manager->getRepository(User::class);
    }
}
