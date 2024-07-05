<?php

declare(strict_types=1);

namespace MezzioSecurity\Session;

use Psr\Container\ContainerInterface;

class SecureSession
{
    static public function secureSessionInit(): void
    {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.sid_length', '255');
        ini_set('session.sid_bits_per_character', '6');
        ini_set('session.name', 'sessiontoken');
        ini_set('session.use_strict_mode', true);
    }

    static public function InitSessionDatabaseHandler(ContainerInterface $container): void
    {
        $handler = $container->get(SessionHandler::class);
        \session_set_save_handler($handler, true);
    }

}