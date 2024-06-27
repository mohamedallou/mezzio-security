<?php

declare(strict_types=1);

namespace MezzioSecurity\RequestHandler;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Uri;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\RetrieveSession;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginUser implements RequestHandlerInterface
{
    public function __construct(
        private readonly PhpSession $adapter
    ) {
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $session  = RetrieveSession::fromRequestOrNull($request);

        if ($session === null) {
            throw new \RuntimeException('No session found');
        }

        $redirect = $this->getRedirect($request);

        // Handle submitted credentials
        return $this->handleLoginAttempt($request, $session, $redirect);
    }

    private function getRedirect(
        ServerRequestInterface $request,
    ) : string {

        $redirect = new Uri($request->getHeaderLine('Referer'));
        if (in_array($redirect->getPath(), ['', '/api/security/user/login'], true)) {
            $redirect = '/';
        }

        return (string) $redirect;
    }

    private function handleLoginAttempt(
        ServerRequestInterface $request,
        SessionInterface $session,
        string $redirect
    ) : ResponseInterface {
        // User session takes precedence over user/pass POST in
        // the auth adapter so we remove the session prior
        // to auth attempt
        $session->unset(UserInterface::class);

        // Login was successful
        if ($this->adapter->authenticate($request)) {
            return new JsonResponse(
                [
                    'success' => true,
                    'redirect' => $redirect,
                ]
            );
        }

        // Login failed
        return new JsonResponse(
            [
                'error' => 'Invalid credentials; please try again',
            ],
            StatusCodeInterface::STATUS_UNAUTHORIZED,
        );
    }
}