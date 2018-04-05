<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Pipeline\Entities;

use Doctrine\ORM\Mapping as ORM;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="pipeline")
 **/
class Pipeline
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $id;
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;
    /**
     * @var Pipe[]
     * @ORM\ManyToMany(targetEntity="\SlayerBirden\DataFlowServer\Pipeline\Entities\Pipe")
     */
    private $pipes;
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Domain\Entities\User")
     */
    private $owner;
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Pipe[]
     */
    public function getPipes(): array
    {
        return $this->pipes;
    }

    /**
     * @param Pipe[] $pipes
     */
    public function setPipes(array $pipes): void
    {
        $this->pipes = $pipes;
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
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
