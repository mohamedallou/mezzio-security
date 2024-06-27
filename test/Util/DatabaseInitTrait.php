<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Util;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use MezzioSecurity\Entity\User;

trait DatabaseInitTrait
{
    private function initDatabase(): EntityManager
    {
        $paths = [
            dirname(__DIR__, 2) . '/src/Entity'
        ];

        // the connection configuration
        $dbParams = [
            'driver'   => 'pdo_sqlite',
            'memory' => true,
        ];

        $config = ORMSetup::createAttributeMetadataConfiguration($paths, true);
        $connection = DriverManager::getConnection($dbParams, $config);
        $entityManager =  new EntityManager($connection, $config);

        $tool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $classes = array(
            $entityManager->getClassMetadata(User::class),
        );
        $tool->createSchema($classes);

        return $entityManager;
    }
}