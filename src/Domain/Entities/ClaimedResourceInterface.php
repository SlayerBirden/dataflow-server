<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Entities;

interface ClaimedResourceInterface
{
    const OWNER_PARAM = 'owner';

    public function getOwner(): User;
}
