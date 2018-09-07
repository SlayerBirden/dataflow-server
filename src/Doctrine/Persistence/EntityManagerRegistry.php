<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

interface EntityManagerRegistry extends ManagerRegistry
{
    /**
     * {@inheritdoc}
     *
     * Can not return null. Will return instance of EntityManagerInterface
     *
     * @return EntityManagerInterface
     * @throws ORMException
     */
    public function getManager($name = null): EntityManagerInterface;

    /**
     * {@inheritdoc}
     *
     * Can not return null. Will return instance of EntityManagerInterface
     *
     * @return EntityManagerInterface
     * @throws ORMException
     */
    public function getManagerForClass($class): EntityManagerInterface;

    /**
     * {@inheritdoc}
     *
     * @return EntityManagerInterface[] An array of EntityManager instances
     */
    public function getManagers();
}
