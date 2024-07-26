<?php

declare(strict_types=1);

namespace MezzioSecurity\Middleware\Factory;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\AuthenticationMiddleware;
use Mezzio\Authentication\Basic\BasicAccess;
use Mezzio\Authentication\Exception\InvalidConfigException;
use MezzioSecurity\Middleware\BasicAuthenticationMiddleware;
use MezzioSecurity\Middleware\JwtAuthenticationMiddleware;
use MezzioSecurity\Service\Authentication\JwtAuthenticationService;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class JwtAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): JwtAuthenticationMiddleware
    {
        $authentication = $container->has(JwtAuthenticationService::class)
            ? $container->get(JwtAuthenticationService::class)
            : null;

        Assert::nullOrIsInstanceOf($authentication, AuthenticationInterface::class);

        if (null === $authentication) {
            throw new InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }

        return new JwtAuthenticationMiddleware($authentication);
    }
}