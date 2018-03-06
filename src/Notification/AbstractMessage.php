<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Notification;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * Type of the message
     *
     * @var string
     */
    protected $type;
    /**
     * Message
     *
     * @var string
     */
    protected $message;
    /**
     * Message identifier (unique id)
     *
     * @var string
     */
    protected $id;

    /**
     * @inheritdoc
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        if (!isset( $this->id)) {
            $this->id = uniqid();
        }
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'class' => $this->getType(),
            'message' => $this->getMessage(),
        ];
    }
}
