<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Hydrator;

use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use Zend\Hydrator\HydrationInterface;

class PasswordHydrator implements HydrationInterface
{
    /**
     * @var PasswordManagerInterface
     */
    private $passwordManager;

    public function __construct(PasswordManagerInterface $passwordManager)
    {
        $this->passwordManager = $passwordManager;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(array $data, $object): Password
    {
        if (empty($data['password'])) {
            throw new \InvalidArgumentException('Can not find password among the data.');
        }
        if (empty($data['owner'])) {
            throw new \InvalidArgumentException('No user provided. Can not set password without a user.');
        }
        if (!($object instanceof Password)) {
            throw new \InvalidArgumentException('Only Password object can be hydrated by this service.');
        }

        $now = new \DateTime();
        // password due in 1 Year
        $due = (new \DateTime())->add(new \DateInterval('P1Y'));

        $object->setActive(isset($data['active']) ? (bool)$data['active'] : true);
        $object->setCreatedAt($now);
        $object->setDue($due);
        $object->setOwner($data['owner']);
        $object->setHash($this->passwordManager->getHash($data['password']));

        return $object;
    }
}
