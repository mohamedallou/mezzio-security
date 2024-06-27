<?php

declare(strict_types=1);

namespace MezzioSecurity\Service\Authorization;

use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

interface OwnerShipAssertionInterface
{
    public function isOwner(UserInterface $user, ServerRequestInterface $request): bool;
}