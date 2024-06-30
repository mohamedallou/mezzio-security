<?php

declare(strict_types=1);

namespace MezzioSecurity\Service\Authentication;


use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CustomPhpSession implements AuthenticationInterface
{

    public function __construct(private readonly AuthenticationInterface $adapter)
    {
    }

    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->hasHeader('Accept')) {
            $accept = $request->getHeaderLine('Accept');

            if (str_contains($accept, 'application/json')) {
                return new JsonResponse(
                    [
                        'redirect' => '/login'
                    ],
                    401
                );
            }
        }

        return $this->adapter->unauthorizedResponse($request);
    }

    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        return $this->adapter->authenticate($request);
    }
}
