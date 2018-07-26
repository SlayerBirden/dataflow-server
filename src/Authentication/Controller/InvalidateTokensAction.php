<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\HydratorInterface;

class InvalidateTokensAction implements MiddlewareInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger, HydratorInterface $hydrator)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();

        $users = $data['users'] ?? [];
        return $this->invalidate($users);
    }

    /**
     * @param array $users
     * @return ResponseInterface
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function invalidate(array $users = []): ResponseInterface
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('active', true));
        if (!empty($users)) {
            $criteria->andWhere(Criteria::expr()->in('owner', $this->getUsers($users)));
        }

        $collection = $this->entityManager
            ->getRepository(Token::class)
            ->matching($criteria);

        if ($collection->count() === 0) {
            return new JsonResponse([
                'data' => [
                    'count' => 0,
                    'tokens' => [],
                ],
                'success' => false,
                'msg' => new SuccessMessage('No tokens found to invalidate for given criteria.'),
            ], 400);
        }
        foreach ($collection as $token) {
            $token->setActive(false);
            $this->entityManager->persist($token);
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'data' => [
                'count' => $collection->count(),
                'tokens' => array_map([$this->hydrator, 'extract'], $collection->toArray()),
            ],
            'success' => true,
            'msg' => new SuccessMessage('Tokens have been deactivated.'),
        ], 200);
    }

    private function getUsers(array $users): array
    {
        $collection = $this->entityManager
            ->getRepository(User::class)
            ->matching(
                Criteria::create()->where(Criteria::expr()->in('id', $users))
            );
        return $collection->toArray();
    }
}
