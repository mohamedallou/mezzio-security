<?php

declare(strict_types=1);

namespace MezzioSecurity\Event;

use Laminas\EventManager\Event;
use MezzioSecurity\Entity\User;

class UserUpdatedEvent extends Event
{
    public function __construct(User $user)
    {
        parent::__construct(static::class, $user);
    }

    public function getTarget(): User
    {
        /** @var User $user */
        $user = parent::getTarget();
        return $user;
    }
}