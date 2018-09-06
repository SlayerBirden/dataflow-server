<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Notification;

final class SuccessMessage extends AbstractMessage
{
    /**
     * @var string
     */
    protected $type = 'success';
}
