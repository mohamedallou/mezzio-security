<?php

declare(strict_types=1);

namespace MezzioSecurity\Session\Factory;

use Doctrine\ORM\EntityManagerInterface;
use MezzioSecurity\Session\SessionHandler;
use Psr\Container\ContainerInterface;

class SessionHandlerFactory
{
    public function __invoke(ContainerInterface $container): SessionHandler
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);
        return new SessionHandler($entityManager);
    }
}