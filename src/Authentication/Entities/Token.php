<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Entities;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tokens")
 */
class Token implements ClaimedResourceInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @var string
     */
    private $token;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    private $active = false;
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
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Domain\Entities\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @var User
     */
    private $owner;
    /**
     * @ORM\OneToMany(targetEntity="\SlayerBirden\DataFlowServer\Authentication\Entities\Grant", mappedBy="token")
     * @var Grant[]
     */
    private $grants;

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
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
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

    /**
     * @return Collection|Grant[]
     */
    public function getGrants(): Collection
    {
        return $this->grants;
    }

    /**
     * @param Collection|Grant[] $grants
     */
    public function setGrants(Collection $grants): void
    {
        $this->grants = $grants;
    }
}
