<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Entities;

use Doctrine\ORM\Mapping as ORM;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

/**
 * @ORM\Entity()
 * @ORM\Table(name="passwords")
 */
class Password implements ClaimedResourceInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    private $hash;
    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $createdAt;
    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    private $due;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $active = false;
    /**
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Domain\Entities\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @var User
     */
    private $owner;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getDue(): \DateTime
    {
        return $this->due;
    }

    /**
     * @param \DateTime $due
     */
    public function setDue(\DateTime $due): void
    {
        $this->due = $due;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return User
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }
}
