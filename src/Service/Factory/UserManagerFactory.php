<?php

declare(strict_types=1);

namespace MezzioSecurity\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Hydrator\ArraySerializableHydrator;
use Laminas\InputFilter\InputFilterPluginManager;
use MezzioSecurity\Service\UserManager;
use Psr\Container\ContainerInterface;

class UserManagerFactory
{
    public function __invoke(ContainerInterface $container): UserManager
    {
        return new UserManager(
            $container->get(EntityManagerInterface::class),
            $container->get(EventManagerInterface::class),
            $container->get(InputFilterPluginManager::class),
            new ArraySerializableHydrator(),
        );
    }
}