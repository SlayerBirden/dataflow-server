<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Pipeline\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="pipe_types")
 **/
class Type
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     * @var string
     */
    private $code;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $tablename;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tablename;
    }

    /**
     * @param string $table
     */
    public function setTableName(string $table): void
    {
        $this->tablename = $table;
    }
}
