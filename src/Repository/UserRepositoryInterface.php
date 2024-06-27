<?php

declare(strict_types=1);

namespace MezzioSecurity\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use MezzioSecurity\Entity\User;
use MezzioSecurity\Entity\UserInterface;

/**
 * @extends  ObjectRepository<User>
 * @extends  Selectable<int,User>
 * @extends ObjectRepository<User>
 */
interface UserRepositoryInterface extends ObjectRepository, Selectable
{

    public function refreshAccessTimeForUser(UserInterface $user): void;

    public function activateUserByToken(string $doiToken): void;
}
