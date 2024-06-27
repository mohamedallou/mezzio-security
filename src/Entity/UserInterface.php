<?php

declare(strict_types=1);

namespace MezzioSecurity\Entity;

use MezzioSecurity\Dto\UserDto;

interface UserInterface
{
    /**
     * @param int $id
     */
    public function setId(int $id): void;

    /**
     * @return string|null
     */
    public function getFirstName(): ?string;

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void;

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @param string $password
     */
    public function setPassword(string $password): void;

    /**
     * @return string
     */
    public function getUsername(): string;

    /**
     * @param string $username
     */
    public function setUsername(string $username): void;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $email
     */
    public function setEmail(string $email): void;

    /**
     * @return string|null
     */
    public function getLastname(): ?string;

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname): void;

    /**
     * @return \DateTime|null
     */
    public function getLastLogin(): ?\DateTime;

    /**
     * @param \DateTime|null $lastLogin
     */
    public function setLastLogin(?\DateTime $lastLogin): void;

    /**
     * @return \DateTime | null;
     */
    public function getCreated(): ?\DateTime;

    /**
     * @param \DateTime|null $created
     */
    public function setCreated(?\DateTime $created): void;

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @param string $status
     */
    public function setStatus(string $status): void;

    /**
     * @return \DateTime|null
     */
    public function getLastAccess(): ?\DateTime;

    /**
     * @param \DateTime|null $lastAccess
     */
    public function setLastAccess(?\DateTime $lastAccess): void;

    /**
     * @return bool
     */
    public function isAdmin(): bool;

    /**
     * @param bool $admin
     * @return User
     */
    public function setAdmin(bool $admin): User;

    public function getId(): int;

    /**
     * @return string|null
     */
    public function getUserDoiHash(): ?string;

    /**
     * @param string|null $userDoiHash
     */
    public function setUserDoiHash(?string $userDoiHash): void;

    public function activate(): void;

    public function isActive(): bool;

    /** @return array<string> */
    public function getPermissions(): array;

    public function addPermission(string $permission): void;

    public function clearPermissions(): void;

    /**
     * @param array<string> $permissions
     * @return void
     */
    public function setPermissions(array $permissions): void;

    /**
     * @return \DateTime|null
     */
    public function getUpdated(): ?\DateTime;

    /**
     * @param \DateTime|null $updated
     */
    public function setUpdated(?\DateTime $updated): void;

    public function jsonSerialize(): mixed;

    public function fillFromDto(UserDto $dto): self;

    public function refreshAccessTime(): void;
}