<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Psr\Log\LoggerInterface;
use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authentication\Exception\InvalidCredentialsException;
use SlayerBirden\DataFlowServer\Authentication\Exception\PasswordExpiredException;
use SlayerBirden\DataFlowServer\Authentication\PasswordManagerInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

final class PasswordManager implements PasswordManagerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Selectable
     */
    private $passwordRepository;

    public function __construct(Selectable $passwordRepository, LoggerInterface $logger)
    {
        $this->passwordRepository = $passwordRepository;
        $this->logger = $logger;
    }

    public function isValidForUser(string $password, User $user): bool
    {
        $results = $this->passwordRepository->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('owner', $user))
                ->andWhere(Criteria::expr()->eq('active', true))
        );
        if ($results->count()) {
            /** @var Password $pw */
            $pw = $results->first();
            return $this->isValid($password, $pw);
        } else {
            throw new InvalidCredentialsException('Invalid login/password combination.');
        }
    }

    /**
     * @param Password $password
     * @return bool
     */
    private function isExpired(Password $password)
    {
        return $password->getDue() < new \DateTime();
    }

    public function getHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @inheritdoc
     */
    public function isValid(string $password, Password $passwordObject): bool
    {
        if ($this->isExpired($passwordObject)) {
            throw new PasswordExpiredException('Password is expired.');
        }
        return password_verify($password, $passwordObject->getHash());
    }
}
