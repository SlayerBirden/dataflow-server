<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication;

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
    public function isValid(string $password, User $user): bool;
    public function getHash(string $password): string;
}
