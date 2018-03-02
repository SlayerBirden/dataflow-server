<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Notification;

class DangerMessage extends AbstractMessage
{
    /**
     * @var string
     */
    protected $type = 'danger';
}
