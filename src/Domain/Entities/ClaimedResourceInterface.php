<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Entities;

interface ClaimedResourceInterface
{
    public function getOwner(): User;
}
