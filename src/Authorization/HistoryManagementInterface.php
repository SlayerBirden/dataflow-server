<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization;

use SlayerBirden\DataFlowServer\Authorization\Entities\History;
use SlayerBirden\DataFlowServer\Authorization\Entities\Permission;

interface HistoryManagementInterface
{
    public function fromPermission(Permission $permission): History;
}
