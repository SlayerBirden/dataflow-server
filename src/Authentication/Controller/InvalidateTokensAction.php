<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Doctrine\Persistence\EntityManagerRegistry;
use SlayerBirden\DataFlowServer\Stdlib\Validation\DataValidationResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralErrorResponseFactory;
use SlayerBirden\DataFlowServer\Stdlib\Validation\GeneralSuccessResponseFactory;
use Zend\Hydrator\HydratorInterface;

final class InvalidateTokensAction implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var EntityManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var Selectable
     */
    private $tokenRepository;
    /**
     * @var Selectable
     */
    private $userRepository;

    public function __construct(
        EntityManagerRegistry $managerRegistry,
        Selectable $tokenRepository,
        Selectable $userRepository,
        LoggerInterface $logger,
        HydratorInterface $hydrator
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->tokenRepository = $tokenRepository;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return (new DataValidationResponseFactory())('tokens', []);
        }

        $users = $data['users'] ?? [];
        try {
            return $this->invalidate($users);
        } catch (\Exception $exception) {
            $this->logger->error((string)$exception);
            return (new GeneralErrorResponseFactory())('Internal error', 'token');
        }
    }

    /**
     * @param array $users
     * @return ResponseInterface
     */
    private function invalidate(array $users = []): ResponseInterface
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('active', true));
        if (!empty($users)) {
            $criteria->andWhere(Criteria::expr()->in('owner', $this->getUsers($users)));
        }

        $collection = $this->tokenRepository->matching($criteria);

        if ($collection->count() === 0) {
            $msg = 'No tokens found to invalidate for given criteria.';
            return (new GeneralErrorResponseFactory())($msg, 'tokens', 400, [], 0);
        }
        $em = $this->managerRegistry->getManagerForClass(Token::class);
        foreach ($collection as $token) {
            $token->setActive(false);
            $em->persist($token);
        }
        $em->flush();
        $msg = 'Tokens have been deactivated.';
        $extracted = array_map([$this->hydrator, 'extract'], $collection->toArray());
        return (new GeneralSuccessResponseFactory())($msg, 'tokens', $extracted, 200, $collection->count());
    }

    private function getUsers(array $users): array
    {
        $collection = $this->userRepository->matching(
            Criteria::create()->where(Criteria::expr()->in('id', $users))
        );
        return $collection->toArray();
    }
}
