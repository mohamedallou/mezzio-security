<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Util;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\ServiceManager;
use MezzioSecurity\ConfigProvider;

trait ContainerInitTrait
{
    private function getContainer(): ServiceManager
    {
        $configAggregator = new ConfigAggregator([
            \Laminas\Diactoros\ConfigProvider::class,
            \Laminas\InputFilter\ConfigProvider::class,
            \Mezzio\LaminasView\ConfigProvider::class,
            \Mezzio\Session\Ext\ConfigProvider::class,
            \Mezzio\Session\ConfigProvider::class,
            \Mezzio\Authentication\Session\ConfigProvider::class,
            \Mezzio\Authentication\ConfigProvider::class,
            \Mezzio\Helper\ConfigProvider::class,
            \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
            \Mezzio\Router\ConfigProvider::class,
            \Mezzio\ConfigProvider::class,
            new ArrayProvider(
                [
                    'dependencies' => [
                        'factories' => [
                            EventManagerInterface::class => function($container) {
                                return new EventManager();
                            },
                            EntityManagerInterface::class => function ($container) {
                                return $this->initDatabase();
                            },
                        ]
                    ]
                ]
            ),
            ConfigProvider::class,
        ]);

        $config = $configAggregator->getMergedConfig();
        $dependencies                       = $config['dependencies'];
        $dependencies['services']['config'] = $config;
        return new ServiceManager($dependencies);
    }
}