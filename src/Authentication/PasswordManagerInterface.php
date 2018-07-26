<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication;

use SlayerBirden\DataFlowServer\Authentication\Entities\Password;
use SlayerBirden\DataFlowServer\Authentication\Exception\InvalidCredentialsException;
use SlayerBirden\DataFlowServer\Authentication\Exception\PasswordExpiredException;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

interface PasswordManagerInterface
{
    /**
     * @param string $password
     * @param User $user
     * @return bool
     * @throws InvalidCredentialsException
     * @throws PasswordExpiredException
     */
    public function isValidForUser(string $password, User $user): bool;

    /**
     * @param string $password
     * @param Password $passwordObject
     * @return bool
     * @throws PasswordExpiredException
     */
    public function isValid(string $password, Password $passwordObject): bool;

    public function getHash(string $password): string;
}
