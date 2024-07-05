<?php

declare(strict_types=1);

namespace MezzioSecurity\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

#[HasLifecycleCallbacks]
#[Entity]
#[Table(name: "`session`")]
#[Index(columns: ["access"], name: "access_idx")]
class Session
{
    #[Id, Column(name: "id", type: "string", length: 400, nullable: false)]
    private string $id;

    #[Column(name: "data", type: Types::TEXT, nullable: false)]
    private string $data = '';

    #[Column(name: "access", type: Types::INTEGER)]
    private int $access;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getAccess(): int
    {
        return $this->access;
    }

    /**
     * @param int $access
     */
    public function setAccess(int $access): void
    {
        $this->access = $access;
    }
}