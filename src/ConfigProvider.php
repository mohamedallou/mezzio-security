<?php

declare(strict_types=1);

namespace MezzioSecurity;

use Laminas\Hydrator\ArraySerializableHydrator;
use Laminas\InputFilter\InputFilterAbstractServiceFactory;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Session\Ext\PhpSessionPersistence;
use Mezzio\Session\SessionPersistenceInterface;
use MezzioSecurity\Middleware\BasicAuthenticationMiddleware;
use MezzioSecurity\Middleware\Factory\BasicAuthenticationMiddlewareFactory;
use MezzioSecurity\RequestHandler\DeleteUser;
use MezzioSecurity\RequestHandler\Factory\LoginUserFactory;
use MezzioSecurity\RequestHandler\Factory\View\LoginFactory;
use MezzioSecurity\RequestHandler\GetUser;
use MezzioSecurity\RequestHandler\LoginUser;
use MezzioSecurity\RequestHandler\Permissions\AssignUserPermission;
use MezzioSecurity\RequestHandler\RegisterUser;
use MezzioSecurity\RequestHandler\UpdateUser;
use MezzioSecurity\RequestHandler\View\Login;
use MezzioSecurity\Service\Authentication\CustomPhpSession;
use MezzioSecurity\Service\Authorization\AuthorizationService;
use MezzioSecurity\Service\Factory\UserManagerFactory;
use MezzioSecurity\Service\UserManager;
use MezzioSecurity\Session\Factory\SessionHandlerFactory;
use MezzioSecurity\Session\SessionHandler;
use MezzioSecurity\Session\SessionPersistence\CustomSessionPersistence;
use Psr\Container\ContainerInterface;

// TODO: add JWT Token Auth and blacklist
// TODO: add IP Restriction
// TODO: Block user after 5 failed login attempts
class ConfigProvider
{
    /**
     * @return array<string,mixed>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'input_filters' => [
                'abstract_factories' => [
                    InputFilterAbstractServiceFactory::class,
                ],
            ],
            'input_filter_specs' => $this->getInputFilters(),
            'routes' => require dirname(__DIR__, 1) . '/config/routes.php',
            'entities' => [
                realpath(__DIR__ . '/Entity/'),
            ],
            'token_ttl' => 300,
            'commands' => [
            ],
            'authentication' => [
                'redirect' => '/login',
            ],
            ConfigAbstractFactory::class => [
                GetUser::class => [
                    UserManager::class,
                ],
                RegisterUser::class => [
                    UserManager::class,
                    ArraySerializableHydrator::class,
                ],
                UpdateUser::class => [
                    UserManager::class,
                    ArraySerializableHydrator::class,
                ],
                DeleteUser::class => [
                    UserManager::class,
                ],
                AssignUserPermission::class => [
                    UserManager::class,
                ],
                CustomPhpSession::class => [
                    PhpSession::class,
                ],
                CustomSessionPersistence::class => [
                    PhpSessionPersistence::class,
                ]
            ]
        ];
    }

    /**
     * Returns the container dependencies
     * @return array{'invokables': array<string,mixed>, 'factories': array<string,mixed>}
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
            ],
            'factories'  => [
                UserManager::class => UserManagerFactory::class,
                SessionHandler::class => SessionHandlerFactory::class,
                ArraySerializableHydrator::class => InvokableFactory::class,
                Login::class => LoginFactory::class,
                BasicAuthenticationMiddleware::class => BasicAuthenticationMiddlewareFactory::class,
                LoginUser::class => LoginUserFactory::class,
                AuthorizationService::class => function (ContainerInterface $container): AuthorizationService {
                    return new AuthorizationService($container);
                },
                CustomSessionPersistence::class => InvokableFactory::class,
            ],
            'aliases'  => [
                AuthenticationInterface::class => CustomPhpSession::class,
                UserRepositoryInterface::class => UserManager::class,
                AuthorizationInterface::class => AuthorizationService::class,
                SessionPersistenceInterface::class => CustomSessionPersistence::class,
            ],
            'abstract_factories' => [
                ConfigAbstractFactory::class,
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function getInputFilters() : array
    {
        return require dirname(__FILE__, 2) . '/config/input_filters.php';
    }

    /**
     * @return array<string,mixed>
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'security'    => [__DIR__ . '/../templates/mezzio-security'],
                'layout' => [__DIR__ . '/../templates/layout'],
            ],
        ];
    }
}