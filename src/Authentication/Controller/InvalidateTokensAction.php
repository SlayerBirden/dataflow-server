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
use SlayerBirden\DataFlowServer\Notification\DangerMessage;
use SlayerBirden\DataFlowServer\Notification\SuccessMessage;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Hydrator\ExtractionInterface;

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
     * @var ExtractionInterface
     */
    private $extraction;

    public function __construct(EntityManager $entityManager, LoggerInterface $logger, ExtractionInterface $extraction)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->extraction = $extraction;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();

        $users = $data['users'] ?? [];
        $all = $data['all'] ?? false;

        try {
            if ($all) {
                return $this->invalidateAll();
            } elseif (!empty($users)) {
                return $this->invalidateByUsers($users);
            } else {
                return new JsonResponse([
                    'data' => [],
                    'success' => false,
                    'msg' => new DangerMessage('Empty criteria provided.'),
                ], 400);
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            return new JsonResponse([
                'data' => [],
                'success' => false,
                'msg' => new DangerMessage('There was an error while invalidating the tokens.'),
            ], 400);
        }
    }

    /**
     * @return ResponseInterface
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function invalidateAll(): ResponseInterface
    {
        /** @var Token[] $collection */
        $collection = $this->entityManager->getRepository(Token::class)->findAll();
        foreach ($collection as $token) {
            $token->setActive(false);
            $this->entityManager->persist($token);
        }

        $this->entityManager->flush();
        return new JsonResponse([
            'data' => [
                'count' => count($collection),
            ],
            'success' => true,
            'msg' => new SuccessMessage('All tokens have been deactivated.'),
        ], 200);
    }

    /**
     * @param array $users
     * @return ResponseInterface
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function invalidateByUsers(array $users): ResponseInterface
    {
        $collection = $this->entityManager
            ->getRepository(Token::class)
            ->matching(
                Criteria::create()->where(Criteria::expr()->in('user', $this->getUsers($users)))
            );
        foreach ($collection as $token) {
            $token->setActive(false);
            $this->entityManager->persist($token);
        }
        $this->entityManager->flush();
        return new JsonResponse([
            'data' => [
                'count' => $collection->count(),
                'tokens' => array_map([$this->extraction, 'extract'], $collection->toArray()),
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
