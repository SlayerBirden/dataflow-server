<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Db\Entities;

use Doctrine\ORM\Mapping as ORM;
use SlayerBirden\DataFlowServer\Domain\Entities\ClaimedResourceInterface;
use SlayerBirden\DataFlowServer\Domain\Entities\User;

/**
 * @ORM\Entity @ORM\Table(name="configuration")
 **/
class DbConfiguration implements ClaimedResourceInterface
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
     **/
    private $title;
    /**
     * @ORM\ManyToOne(targetEntity="\SlayerBirden\DataFlowServer\Domain\Entities\User")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     **/
    private $owner;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     **/
    private $dbname;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     **/
    private $user;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     **/
    private $password;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     **/
    private $host;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     **/
    private $driver;
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer|null
     **/
    private $port;
    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     **/
    private $url;

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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
     * @return string|null
     */
    public function getDbname(): ?string
    {
        return $this->dbname;
    }

    /**
     * @param string $dbname
     */
    public function setDbname(string $dbname): void
    {
        $this->dbname = $dbname;
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string|null
     */
    public function getDriver(): ?string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     */
    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
