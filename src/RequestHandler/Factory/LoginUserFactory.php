<?php

declare(strict_types=1);

namespace MezzioSecurity\RequestHandler\Factory;

use Mezzio\Authentication\AuthenticationInterface;
use MezzioSecurity\RequestHandler\LoginUser;
use Psr\Container\ContainerInterface;

class LoginUserFactory
{
    public function __invoke(ContainerInterface $container): LoginUser
    {
        return new LoginUser(
            $container->get(AuthenticationInterface::class),
        );
    }
}