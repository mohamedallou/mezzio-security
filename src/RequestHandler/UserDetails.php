<?php

declare(strict_types=1);

namespace MezzioSecurity\RequestHandler;

use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserDetails implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var UserInterface $user */
        $user = $request->getAttribute(UserInterface::class);
        return new JsonResponse([
            'username' => $user->getIdentity(),
            'permissions' => $user->getRoles(),
            'details' => $user->getDetails(),
            'admin' => $user->getDetail('admin'),
        ]);
    }
}
