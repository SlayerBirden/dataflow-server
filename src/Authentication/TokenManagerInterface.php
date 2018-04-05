<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication;

use SlayerBirden\DataFlowServer\Authentication\Entities\Token;
use SlayerBirden\DataFlowServer\Authentication\Exception\InvalidCredentialsException;
use SlayerBirden\DataFlowServer\Authentication\Exception\PermissionDeniedException;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

interface TokenManagerInterface
{
    /**
     * @param string $user
     * @param string $password
     * @param array $resources
     * @return Token
     * @throws InvalidCredentialsException
     * @throws PermissionDeniedException
     */
    public function getToken(string $user, string $password, array $resources): Token;

    public function getTmpToken(User $user, array $resources): Token;
}
