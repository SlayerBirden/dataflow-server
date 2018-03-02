<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Notification;

class SuccessMessage extends AbstractMessage
{
    /**
     * @var string
     */
    protected $type = 'success';
}
