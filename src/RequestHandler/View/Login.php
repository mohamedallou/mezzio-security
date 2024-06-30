<?php

declare(strict_types=1);

namespace MezzioSecurity\RequestHandler\View;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\RetrieveSession;
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Login implements RequestHandlerInterface
{
    private const REDIRECT_ATTRIBUTE = 'authentication:redirect';

    public function __construct(
        private readonly TemplateRendererInterface $renderer,
        private readonly AuthenticationInterface $adapter
    ) {
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $session  = RetrieveSession::fromRequestOrNull($request);

        if ($session === null) {
            throw new \RuntimeException('No session found');
        }

        $redirect = $this->getRedirect($request, $session);

        // Handle submitted credentials
        if ('POST' === $request->getMethod()) {
            return $this->handleLoginAttempt($request, $session, $redirect);
        }

        // Display initial login form
        $session->set(self::REDIRECT_ATTRIBUTE, $redirect);

        return new HtmlResponse($this->renderer->render(
            'security::login',
            [
                'layout' => 'layout::security'
            ]
        ));
    }

    private function getRedirect(
        ServerRequestInterface $request,
        SessionInterface $session
    ) : string {
        $redirect = $session->get(self::REDIRECT_ATTRIBUTE);

        if (! $redirect) {
            $redirect = new Uri($request->getHeaderLine('Referer'));
            if (in_array($redirect->getPath(), ['', '/login'], true)) {
                $redirect = '/';
            }
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
            $session->unset(self::REDIRECT_ATTRIBUTE);
            return new RedirectResponse($redirect);
        }

        // Login failed
        return new HtmlResponse(
            $this->renderer->render(
                'security::login',
                ['error' => 'Invalid credentials; please try again']
            ),
            StatusCodeInterface::STATUS_UNAUTHORIZED
        );
    }
}