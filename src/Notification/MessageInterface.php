<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Notification;

interface MessageInterface extends \JsonSerializable
{
    public function getType(): string;

    public function getMessage(): string;

    public function getId(): string;
}
