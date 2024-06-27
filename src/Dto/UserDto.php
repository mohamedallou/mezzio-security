<?php

declare(strict_types=1);

namespace MezzioSecurity\Dto;

class UserDto
{
    public ?string $email = null;
    public ?string $username = null;
    public ?string $password = null;
    public ?string $firstName = null;
    public ?string $lastName = null;

    /**
     * @return array{email: string, username: string, password:string}
     */
    public function getArrayCopy(): array
    {
        return [
            'email' => $this->email,
            'username' => $this->username,
            'password' => $this->password,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
        ];
    }

    public function exchangeArray(array $data): void
    {
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->email = $data['email'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
    }
}