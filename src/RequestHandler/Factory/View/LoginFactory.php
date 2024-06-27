<?php

declare(strict_types=1);

namespace MezzioSecurity\RequestHandler\Factory\View;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Template\TemplateRendererInterface;
use MezzioSecurity\RequestHandler\View\Login;
use Psr\Container\ContainerInterface;

class LoginFactory
{
    public function __invoke(ContainerInterface $container): Login
    {
        return new Login(
            $container->get(TemplateRendererInterface::class),
            $container->get(AuthenticationInterface::class),
        );
    }
}