<?php

namespace codecept\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module\REST;
use Codeception\TestInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Grant;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Authorization\Service\ResourceManager;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class Api extends \Codeception\Module
{
    /**
     * @var int
     */
    private $userId;
    /**
     * @var int
     */
    private $tokenId;

    /**
     * @param TestInterface $test
     * @throws \Codeception\Exception\ModuleException
     */
    public function _before(TestInterface $test)
    {
        /** @var ZendExpressive3 $ze3I */
        $ze3I = $this->getModule('\\' . ZendExpressive3::class);

        /** @var ResourceManager $resourceManager */
        $resourceManager = $ze3I->container->get(ResourceManager::class);

        $resources = $resourceManager->getAllResources();

        /** @var CleanDoctrine2 $doctrineI */
        $doctrineI = $this->getModule('\\' . CleanDoctrine2::class);

        $this->userId = $doctrineI->haveInRepository(User::class, [
            'first' => 'Tester',
            'last' => 'Tester',
            'email' => 'test1@example.com',
        ]);

        $user = $doctrineI->grabEntityFromRepository(User::class, ['id' => $this->userId]);

        $this->tokenId = $doctrineI->haveInRepository(Token::class, [
            'owner' => $user,
            'token' => 'X-X-X',
            'active' => 1,
            'createdAt' => new \DateTime(),
            'due' => new \DateTime('2130-01-01 00:00:00'),
        ]);

        $this->addPermissions($resources);
        $this->addGrants($resources);

        /** @var REST $I */
        $I = $this->getModule('REST');
        $I->amBearerAuthenticated('X-X-X');
    }

    /**
     * @param array $resources
     * @throws \Codeception\Exception\ModuleException
     */
    private function addPermissions(array $resources)
    {
        /** @var CleanDoctrine2 $I */
        $I = $this->getModule('\\' . CleanDoctrine2::class);
        $user = $I->grabEntityFromRepository(User::class, ['id' => $this->userId]);

        foreach ($resources as $resource) {
            $I->haveInRepository(Permission::class, [
                'user' => $user,
                'resource' => $resource,
            ]);
        }
    }

    /**
     * @param array $resources
     * @throws \Codeception\Exception\ModuleException
     */
    private function addGrants(array $resources)
    {
        /** @var CleanDoctrine2 $I */
        $I = $this->getModule('\\' . CleanDoctrine2::class);
        $token = $I->grabEntityFromRepository(Token::class, ['id' => $this->tokenId]);

        foreach ($resources as $resource) {
            $I->haveInRepository(Grant::class, [
                'token' => $token,
                'resource' => $resource,
            ]);
        }
    }
}
