<?php

declare(strict_types=1);

use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use MezzioSecurity\RequestHandler\GetUser;
use MezzioSecurity\RequestHandler\LoginUser;
use MezzioSecurity\RequestHandler\Permissions\AssignUserPermission;
use MezzioSecurity\RequestHandler\RegisterUser;
use MezzioSecurity\RequestHandler\UpdateUser;

return [
    'security.register_user' => [
        'path'            => '/api/security/user/register[/]',
        'middleware'      => [RegisterUser::class],
        'allowed_methods' => ['POST'],
        'name'            => 'security.register_user',
    ],
    'security.get_user' => [
        'path'            => '/api/security/user[/{id}]',
        'middleware'      => [GetUser::class],
        'allowed_methods' => ['GET'],
        'name'            => 'security.get_user',
    ],
    'security.update_user' => [
        'path'            => '/api/security/user/{id}[/]',
        'middleware'      => [UpdateUser::class],
        'allowed_methods' => ['PATCH'],
        'name'            => 'security.update_user',
    ],
    'security.delete_user' => [
        'path'            => '/api/security/user/{id}[/]',
        'middleware'      => [UpdateUser::class],
        'allowed_methods' => ['DELETE'],
        'name'            => 'security.delete_user',
    ],
    'security.login_user' => [
        'path'            => '/api/security/user/login[/]',
        'middleware'      => [
            BodyParamsMiddleware::class,
            LoginUser::class
        ],
        'allowed_methods' => ['POST'],
        'name'            => 'security.login_user',
    ],
    'security.add_user_permissions' => [
        'path'            => '/api/security/user/{id}/permissions[/]',
        'middleware'      => [
            AssignUserPermission::class
        ],
        'allowed_methods' => ['PUT'],
        'name'            => 'security.add_user_permissions',
    ],
];
