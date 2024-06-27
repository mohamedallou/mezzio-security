<?php

declare(strict_types=1);

namespace MezzioSecurity\Middleware\Factory;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\Basic\BasicAccess;
use Mezzio\Authentication\Exception\InvalidConfigException;
use MezzioSecurity\Middleware\BasicAuthenticationMiddleware;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class BasicAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationMiddleware
    {
        $authentication = $container->has(BasicAccess::class)
            ? $container->get(BasicAccess::class)
            : null;

        Assert::nullOrIsInstanceOf($authentication, AuthenticationInterface::class);

        if (null === $authentication) {
            throw new InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }

        return new BasicAuthenticationMiddleware($authentication);
    }
}