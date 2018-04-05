<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Entities;

use Doctrine\ORM\Mapping as ORM;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

/**
 * @ORM\Entity()
 * @ORM\Table(name="permission_history")
 */
class History implements ClaimedResourceInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;
    /**
     * User who've made the change.
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Domain\Entities\User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @var User
     */
    private $owner;
    /**
     * User with permissions
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Domain\Entities\User")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $user;
    /**
     * Resource address
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    private $resource;
    /**
     * What was done: added/modified/deleted
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    private $changeAction;
    /**
     * When was the change made.
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $at;
    /**
     * Link to existing permission (if still exists)
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Authorization\Entities\Permission")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @var Permission
     */
    private $permission;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     */
    public function setResource(string $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getChangeAction(): string
    {
        return $this->changeAction;
    }

    /**
     * @param string $changeAction
     */
    public function setChangeAction(string $changeAction): void
    {
        $this->changeAction = $changeAction;
    }

    /**
     * @return \DateTime
     */
    public function getAt(): \DateTime
    {
        return $this->at;
    }

    /**
     * @param \DateTime $at
     */
    public function setAt(\DateTime $at): void
    {
        $this->at = $at;
    }

    /**
     * @return Permission
     */
    public function getPermission(): Permission
    {
        return $this->permission;
    }

    /**
     * @param Permission $permission
     */
    public function setPermission(Permission $permission): void
    {
        $this->permission = $permission;
    }
}
