<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Doctrine\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use SlayerBirden\DataFlowServer\Db\Entities\DbConfiguration;
use SlayerBirden\DataFlowServer\Validation\Exception\ValidationException;

final class Validation implements EventSubscriber
{
    /**
     * @inheritDoc
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate,
            Events::prePersist,
        ];
    }

    /**
     * @param PreUpdateEventArgs $args
     * @throws ValidationException
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($this->shouldHandle($entity)) {
            $this->validate($entity);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ValidationException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($this->shouldHandle($entity)) {
            $this->validate($entity);
        }
    }

    /**
     * Check if object should be handled
     *
     * @param object $entity
     * @return bool
     */
    private function shouldHandle($entity): bool
    {
        return $entity instanceof DbConfiguration;
    }

    /**
     * @param DbConfiguration $configuration
     * @throws ValidationException
     */
    private function validate(DbConfiguration $configuration): void
    {
        $msg = '%s is required field if "url" is not set.';
        if (!empty($configuration->getUrl())) {
            return;
        }

        if (empty($configuration->getDbname())) {
            throw new ValidationException(sprintf($msg, 'dbname'));
        }
        if (empty($configuration->getDriver())) {
            throw new ValidationException(sprintf($msg, 'driver'));
        }
        if (empty($configuration->getHost())) {
            throw new ValidationException(sprintf($msg, 'host'));
        }
        if (empty($configuration->getUser())) {
            throw new ValidationException(sprintf($msg, 'user'));
        }
        if (empty($configuration->getPort())) {
            throw new ValidationException(sprintf($msg, 'port'));
        }

        if (empty($configuration->getTitle())) {
            throw new ValidationException('Title is empty.');
        }
    }
}
