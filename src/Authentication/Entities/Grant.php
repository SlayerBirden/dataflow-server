<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Authentication\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="grants")
 */
class Grant
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
    private $resource;
    /**
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Authentication\Entities\Token", inversedBy="grants")
     * @ORM\JoinColumn(nullable=false)
     * @var Token
     */
    private $token;

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
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token): void
    {
        $this->token = $token;
    }
}
