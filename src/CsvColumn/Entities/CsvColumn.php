<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\CsvColumn\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="csv_columns")
 **/
class CsvColumn
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer|null
     **/
    private $id;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $field;
    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $order = 0;

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
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField(string $field): void
    {
        $this->field = $field;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }
}
