<?php

declare(strict_types=1);

namespace MezzioSecurity\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use MezzioSecurity\Dto\UserDto;
use MezzioSecurity\Repository\UserRepository;

#[Entity(repositoryClass: UserRepository::class, readOnly: false)]
#[HasLifecycleCallbacks]
#[Table(name: "user")]
#[Index(columns: ["user_firstname", "user_lastname"], name: "user_name_idx")]
class User implements \JsonSerializable, UserInterface
{
    public const STATUS_ACTIVE = 'active';

    #[Id, Column(name: "user_id", type: "integer"), GeneratedValue(strategy: "IDENTITY")]
    protected int $id;

    #[Column(name: "user_firstname", type: "string", nullable: true)]
    protected ?string $firstName;

    #[Column(name: "user_password", type: "string", nullable: true)]
    protected ?string $password;

    #[Column(name: "user_username", type: "string", unique: true, nullable: true)]
    protected ?string $username;

    #[Column(name: "user_email", type: "string", unique: true, nullable: false)]
    protected string $email;

    #[Column(name: "user_lastname", type: "string", nullable: true)]
    protected ?string $lastname;

    #[Column(name: "user_doi_hash", type: "string", nullable: true)]
    protected ?string $userDoiHash;

    #[Column(name: "user_lastlogin", type: "datetime", nullable: true)]
    protected ?\DateTime $lastLogin;

    #[Column(name: "user_created", type: "datetime", nullable: true)]
    protected ?\DateTime $created;

    #[Column(name: "user_updated", type: "datetime", nullable: true)]
    protected ?\DateTime $updated;

    #[Column(name: "user_status", type: "string", nullable: true)]
    protected ?string $status;

    #[Column(name: "user_lastip", type: "string", nullable: true)]
    protected ?string $lastAccessIpAddress;

    #[Column(name: "user_last_access", type: "datetime", nullable: true)]
    protected ?\DateTime $lastAccess;

    #[Column(name: "user_admin", type: "boolean", nullable: false)]
    protected bool $admin = false;

    #[Column(name: "user_permissions", type: "simple_array", nullable: true)]
    protected array $permissions = [];

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName ?? null;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password ?? '';
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username ?? '';
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname ?? null;
    }

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin ?? null;
    }

    /**
     * @param \DateTime|null $lastLogin
     */
    public function setLastLogin(?\DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return \DateTime | null;
     */
    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime|null $created
     */
    public function setCreated(?\DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status ?? null;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    #[PrePersist]
    public function onPrePersist(): void
    {
        $now = Carbon::now();
        $this->created = $now;
        $this->updated = $now;
    }

    #[PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = Carbon::now();
    }

    /**
     * @return \DateTime|null
     */
    public function getLastAccess(): ?\DateTime
    {
        return $this->lastAccess;
    }

    /**
     * @param \DateTime|null $lastAccess
     */
    public function setLastAccess(?\DateTime $lastAccess): void
    {
        $this->lastAccess = $lastAccess;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * @param bool $admin
     * @return User
     */
    public function setAdmin(bool $admin): User
    {
        $this->admin = $admin;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return string|null
     */
    public function getUserDoiHash(): ?string
    {
        return $this->userDoiHash;
    }

    /**
     * @param string|null $userDoiHash
     */
    public function setUserDoiHash(?string $userDoiHash): void
    {
        $this->userDoiHash = $userDoiHash;
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** @return array<string> */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function addPermission(string $permission): void
    {
        if (in_array($permission, $this->permissions)) {
            return;
        }

        $this->permissions[] = $permission;
    }

    public function clearPermissions(): void
    {
        $this->permissions = [];
    }

    /**
     * @param array<string> $permissions
     * @return void
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdated(): ?\DateTime
    {
        return $this->updated;
    }

    /**
     * @param \DateTime|null $updated
     */
    public function setUpdated(?\DateTime $updated): void
    {
        $this->updated = $updated;
    }

    /**
     * @return string|null
     */
    public function getLastAccessIpAddress(): ?string
    {
        return $this->lastAccessIpAddress;
    }

    /**
     * @param string|null $lastAccessIpAddress
     */
    public function setLastAccessIpAddress(?string $lastAccessIpAddress): void
    {
        $this->lastAccessIpAddress = $lastAccessIpAddress;
    }

    public function jsonSerialize(): mixed
    {
        return $this->getDetails();
    }

    public function refreshAccessTime(): void
    {
        $this->lastAccess = new \DateTime();
    }

    public function fillFromDto(UserDto $dto): self
    {
        if (isset($dto->password)) {
            $this->setPassword(password_hash($dto->password, PASSWORD_DEFAULT));
        }

        if (isset($dto->email)) {
            $this->setEmail($dto->email);
        }

        if (isset($dto->username)) {
            $this->setUsername($dto->username);
        }

        if (isset($dto->firstName)) {
            $this->setFirstName($dto->firstName);
        }

        if (isset($dto->lastName)) {
            $this->setLastname($dto->lastName);
        }

        return $this;
    }

    public function getDetails(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'username' => $this->getUsername(),
            'admin' => $this->isAdmin(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastname(),
            'permissions' => $this->getPermissions(),
            'created' => $this->getCreated()?->format('Y-m-d H:i:s'),
            'updated' => $this->getUpdated()?->format('Y-m-d H:i:s'),
        ];
    }
}
