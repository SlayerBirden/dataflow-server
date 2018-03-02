<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="users")
 **/
class User
{
    /**
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     * @var integer|null
     **/
    private $id;
    /**
     * @ORM\Column(type="string")
     * @var string
     **/
    private $first;
    /**
     * @ORM\Column(type="string")
     * @var string
     **/
    private $last;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirst(): string
    {
        return $this->first;
    }

    /**
     * @param string $first
     */
    public function setFirst(string $first): void
    {
        $this->first = $first;
    }

    /**
     * @return string
     */
    public function getLast(): string
    {
        return $this->last;
    }

    /**
     * @param string $last
     */
    public function setLast(string $last): void
    {
        $this->last = $last;
    }
}
