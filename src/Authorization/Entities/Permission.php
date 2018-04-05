<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authorization\Entities;

use Doctrine\ORM\Mapping as ORM;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

/**
 * @ORM\Table(name="permissions")
 * @ORM\Entity
 */
class Permission
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @var integer
     */
    private $id;
    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    private $resource;
    /**
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Domain\Entities\User")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $user;

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
}
