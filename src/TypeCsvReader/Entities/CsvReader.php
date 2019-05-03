<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\TypeCsvReader\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use SlayerBirden\DataFlowServer\CsvColumn\Entities\CsvColumn;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;
use SlayerBirden\DataFlowServer\Pipeline\Entities\Pipe;

/**
 * @ORM\Entity
 * @ORM\Table(name="type_csv_reader")
 **/
class CsvReader implements ClaimedResourceInterface
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
    private $name;
    /**
     * @var Collection|Pipe[]
     * @ORM\OneToMany(targetEntity="\SlayerBirden\DataFlowServer\Pipeline\Entities\Pipe")
     */
    private $pipes;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $filePath;
    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $hasHeaderRow;
    /**
     * @var Collection|CsvColumn[]
     * @ORM\OneToMany(targetEntity="\SlayerBirden\DataFlowServer\CsvColumn\Entities\CsvColumn")
     */
    private $columns;
    /**
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Domain\Entities\User")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $owner;

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
     * @return Collection|Pipe[]
     */
    public function getPipes(): Collection
    {
        return $this->pipes;
    }

    /**
     * @param Collection|Pipe[] $pipes
     */
    public function setPipes($pipes): void
    {
        $this->pipes = $pipes;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * @return bool
     */
    public function isHasHeaderRow(): bool
    {
        return $this->hasHeaderRow;
    }

    /**
     * @param bool $hasHeaderRow
     */
    public function setHasHeaderRow(bool $hasHeaderRow): void
    {
        $this->hasHeaderRow = $hasHeaderRow;
    }

    /**
     * @return Collection|CsvColumn[]
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    /**
     * @param Collection|CsvColumn[] $columns
     */
    public function setColumns($columns): void
    {
        $this->columns = $columns;
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
