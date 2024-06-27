<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Unit\Authorization;

use Mezzio\Authentication\UserInterface;
use MezzioSecurity\Service\Authorization\OwnerShipAssertionInterface;
use Psr\Http\Message\ServerRequestInterface;

class OwnershipAssertionFake implements OwnerShipAssertionInterface
{
    public function isOwner(UserInterface $user, ServerRequestInterface $request): bool
    {
        return true;
    }
}