<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Grant;
use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Authentication\Exception\InvalidCredentialsException;
use SlayerBirden\DataFlowServer\Authentication\Exception\PermissionDeniedException;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Authentication\TokenManagerInterface;
use SlayerBirden\DataFlowServer\Authorization\PermissionManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

class TokenManager implements TokenManagerInterface
{
    /**
     * @var PasswordManagerInterface
     */
    private $passwordManager;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PermissionManagerInterface
     */
    private $permissionManager;

    public function __construct(
        PasswordManagerInterface $passwordManager,
        EntityManager $entityManager,
        LoggerInterface $logger,
        PermissionManagerInterface $permissionManager
    ) {
        $this->passwordManager = $passwordManager;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->permissionManager = $permissionManager;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function getToken(string $user, string $password, array $resources): Token
    {
        try {
            $user = $this->getByUsername($user);
            if ($this->passwordManager->isValidForUser($password, $user)) {
                $token = new Token();
                $now = new \DateTime();
                // default token for 1 month
                $due = (new \DateTime())->add(new \DateInterval('P1M'));

                $token->setActive(true);
                $token->setCreatedAt($now);
                $token->setOwner($user);
                $token->setDue($due);
                $token->setGrants($this->getGrants($token, $resources, $user));
                $token->setToken($this->generateToken());

                $this->entityManager->persist($token);
                $this->entityManager->flush();

                return $token;
            }
        } catch (ORMException $exception) {
            $this->logger->error((string)$exception);
            throw new InvalidCredentialsException('Unknown ORM error.', $exception);
        }

        throw new InvalidCredentialsException('Could not validate credentials.');
    }

    private function getByUsername(string $user): User
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('email', $user));
        $users = $this->entityManager->getRepository(User::class)->matching($criteria);

        if ($users->count()) {
            return $users->first();
        } else {
            throw new InvalidCredentialsException('Could not find user by provided email.');
        }
    }

    /**
     * UUIDv4
     * https://stackoverflow.com/a/15875555/927404
     *
     * @return string
     */
    private function generateToken(): string
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * @param Token $token
     * @param array $resources
     * @param User $user
     * @return ArrayCollection|Grant[]
     * @throws PermissionDeniedException
     * @throws ORMException
     */
    private function getGrants(Token $token, array $resources, User $user): ArrayCollection
    {
        $grants = [];
        foreach ($resources as $resource) {
            if (!$this->permissionManager->isAllowed($resource, $user)) {
                throw new PermissionDeniedException(sprintf('%s is not allowed for provided user.', $resource));
            }
            $grant = new Grant();
            $grant->setToken($token);
            $grant->setResource($resource);
            $this->entityManager->persist($grant);
            $grants[] = $grant;
        }

        return new ArrayCollection($grants);
    }

    /**
     * @param User $user
     * @param array $resources
     * @return Token
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function getTmpToken(User $user, array $resources): Token
    {
        $token = new Token();
        $now = new \DateTime();
        // tmp token for 1 hour
        $due = (new \DateTime())->add(new \DateInterval('PT1H'));

        $token->setActive(true);
        $token->setCreatedAt($now);
        $token->setOwner($user);
        $token->setDue($due);
        $token->setGrants($this->getGrants($token, $resources, $user));
        $token->setToken($this->generateToken());

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }
}
