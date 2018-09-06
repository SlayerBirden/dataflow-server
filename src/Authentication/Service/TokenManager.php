<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ManagerRegistry;
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

final class TokenManager implements TokenManagerInterface
{
    /**
     * @var PasswordManagerInterface
     */
    private $passwordManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PermissionManagerInterface
     */
    private $permissionManager;
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var Selectable
     */
    private $userRepository;

    public function __construct(
        ManagerRegistry $managerRegistry,
        Selectable $userRepository,
        PasswordManagerInterface $passwordManager,
        LoggerInterface $logger,
        PermissionManagerInterface $permissionManager
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->userRepository = $userRepository;
        $this->passwordManager = $passwordManager;
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
            $em = $this->managerRegistry->getManagerForClass(Token::class);
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

                $em->persist($token);
                $em->flush();

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
        $users = $this->userRepository->matching($criteria);

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
     */
    private function getGrants(Token $token, array $resources, User $user): ArrayCollection
    {
        $grants = [];
        $em = $this->managerRegistry->getManagerForClass(Token::class);
        foreach ($resources as $resource) {
            if (!$this->permissionManager->isAllowed($resource, $user)) {
                throw new PermissionDeniedException(sprintf('%s is not allowed for provided user.', $resource));
            }
            $grant = new Grant();
            $grant->setToken($token);
            $grant->setResource($resource);
            $em->persist($grant);
            $grants[] = $grant;
        }

        return new ArrayCollection($grants);
    }

    /**
     * @param User $user
     * @param array $resources
     * @return Token
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

        $em = $this->managerRegistry->getManagerForClass(Token::class);
        $em->persist($token);
        $em->flush();

        return $token;
    }
}
