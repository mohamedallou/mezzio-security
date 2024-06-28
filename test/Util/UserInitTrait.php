<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Util;

use MezzioSecurity\Dto\UserDto;
use MezzioSecurity\Entity\User;
use MezzioSecurity\Service\UserManager;

trait UserInitTrait
{
    public function insertNewUser(string $username, string $mail, string $password): User
    {
        $userDto = new UserDto();
        $userDto->username = $username; // min 5 characters
        $userDto->email = $mail;
        $userDto->password = $password; // min 8 characters

        /** @var UserManager $userManager */
        $userManager = $this->container->get(UserManager::class);

        return $userManager->registerNewUser($userDto);
    }
}