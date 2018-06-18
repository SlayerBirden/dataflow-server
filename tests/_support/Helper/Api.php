<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module\CleanDoctrine2;
use Codeception\Module\REST;
use Codeception\Module\ZendExpressive3;
use Codeception\TestInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Grant;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;
use SlayerBirden\DataFlowServer\Authorization\Service\ResourceManager;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class Api extends \Codeception\Module
{
    /**
     * @param TestInterface $test
     * @throws \Codeception\Exception\ModuleException
     */
    public function _before(TestInterface $test)
    {
        /** @var ZendExpressive3 $ze3I */
        $ze3I = $this->getModule('ZendExpressive3');

        /** @var ResourceManager $resourceManager */
        $resourceManager = $ze3I->container->get(ResourceManager::class);

        $resources = $resourceManager->getAllResources();

        /** @var CleanDoctrine2 $doctrineI */
        $doctrineI = $this->getModule('CleanDoctrine2');

        $doctrineI->haveInRepository(User::class, [
            'id' => 1,
            'first' => 'Tester',
            'last' => 'Tester',
            'email' => 'test1@example.com',
        ]);

        $user = $doctrineI->grabEntityFromRepository(User::class, ['id' => 1]);

        $doctrineI->haveInRepository(Token::class, [
            'id' => 1,
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
        $I = $this->getModule('CleanDoctrine2');
        $user = $I->grabEntityFromRepository(User::class, ['id' => 1]);

        foreach ($resources as $key => $resource) {
            $I->haveInRepository(Permission::class, [
                'id' => ++$key,
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
        $I = $this->getModule('CleanDoctrine2');
        $token = $I->grabEntityFromRepository(Token::class, ['id' => 1]);

        foreach ($resources as $key => $resource) {
            $I->haveInRepository(Grant::class, [
                'id' => ++$key,
                'token' => $token,
                'resource' => $resource,
            ]);
        }
    }
}
