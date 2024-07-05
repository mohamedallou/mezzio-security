<?php

declare(strict_types=1);

namespace MezzioSecurity\Session\SessionPersistence;

use Dflydev\FigCookies\FigRequestCookies;
use Mezzio\Session\Persistence\CacheHeadersGeneratorTrait;
use Mezzio\Session\Persistence\SessionCookieAwareTrait;
use Mezzio\Session\Session;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionPersistenceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Session\InitializePersistenceIdInterface;

class CustomSessionPersistence implements SessionPersistenceInterface, InitializePersistenceIdInterface
{
    use CacheHeadersGeneratorTrait;
    use SessionCookieAwareTrait;

    /**
     * Use non locking mode during session initialization?
     */
    private bool $nonLocking;

    /**
     * Memorize session ini settings before starting the request.
     *
     * The cache_limiter setting is actually "stolen", as we will start the
     * session with a forced empty value in order to instruct the php engine to
     * skip sending the cache headers (this being php's default behaviour).
     * Those headers will be added programmatically to the response along with
     * the session set-cookie header when the session data is persisted.
     *
     * @param bool $nonLocking use the non locking mode during initialization?
     * @param bool $deleteCookieOnEmptySession delete cookie from browser when session becomes empty?
     */
    public function __construct(bool $nonLocking = false, bool $deleteCookieOnEmptySession = false)
    {
        $this->nonLocking                 = $nonLocking;
        $this->deleteCookieOnEmptySession = $deleteCookieOnEmptySession;

        // Get session cache ini settings
        $this->cacheLimiter = ini_get('session.cache_limiter');
        $this->cacheExpire  = (int) ini_get('session.cache_expire');

        // Get session cookie ini settings
        $this->cookieName     = ini_get('session.name');
        $this->cookieLifetime = (int) ini_get('session.cookie_lifetime');
        $this->cookiePath     = ini_get('session.cookie_path');
        $this->cookieDomain   = ini_get('session.cookie_domain');
        $this->cookieSecure   = filter_var(
            ini_get('session.cookie_secure'),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
        $this->cookieHttpOnly = filter_var(
            ini_get('session.cookie_httponly'),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
        $this->cookieSameSite = ini_get('session.cookie_samesite');
    }

    /**
     * @internal
     *
     * @param array $sessionConfig
     */
    final public static function fromConfigArray(array $sessionConfig = []): self
    {
        $ext = $sessionConfig['persistence']['ext'] ?? [];

        $instance = new self(
            ! empty($ext['non_locking']),
            ! empty($ext['delete_cookie_on_empty_session']),
        );

        // Get session cache ini settings
        $instance->cacheLimiter = $sessionConfig['cache_limiter'] ?? ini_get('session.cache_limiter');
        $instance->cacheExpire  = $sessionConfig['cache_expire'] ?? (int) ini_get('session.cache_expire');

        // Get session cookie ini settings
        $instance->cookieName     = $sessionConfig['name'] ?? ini_get('session.name');
        $instance->cookieLifetime = $sessionConfig['cookie_lifetime'] ?? (int) ini_get('session.cookie_lifetime');
        $instance->cookiePath     = $sessionConfig['cookie_path'] ?? ini_get('session.cookie_path');
        $instance->cookieDomain   = $sessionConfig['cookie_domain'] ?? ini_get('session.cookie_domain');
        $instance->cookieSecure   = $sessionConfig['cookie_secure']
            ?? (bool) filter_var(ini_get('session.cookie_secure'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $instance->cookieHttpOnly = $sessionConfig['cookie_httponly']
            ?? (bool) filter_var(ini_get('session.cookie_httponly'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $instance->cookieSameSite = $sessionConfig['cookie_samesite'] ?? ini_get('session.cookie_samesite');

        return $instance;
    }

    /**
     * @internal
     *
     * @return bool the non-locking mode used during initialization
     */
    public function isNonLocking(): bool
    {
        return $this->nonLocking;
    }

    public function initializeSessionFromRequest(ServerRequestInterface $request): SessionInterface
    {
        $sessionId = $this->getSessionCookieValueFromRequest($request);

        if ($sessionId) {
            $this->startSession($sessionId, [
                'read_and_close' => $this->nonLocking,
            ]);
        }
        return new Session($_SESSION ?? [], $sessionId);
    }

    public function persistSession(SessionInterface $session, ResponseInterface $response): ResponseInterface
    {
        $id = $session->getId();

        // Regenerate if:
        // - the session is marked as regenerated
        // - the id is empty, but the data has changed (new session)
        if (
            $session->isRegenerated()
            || ($id === '' && $session->hasChanged())
        ) {
            $id = $this->regenerateSession();
        } elseif ($this->nonLocking && $session->hasChanged()) {
            // we reopen the initial session only if there are changes to write
            $this->startSession($id);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = $session->toArray();
            session_write_close();
        }

        // If we do not have an identifier at this point, it means a new
        // session was created, but never written to. In that case, there's
        // no reason to provide a cookie back to the user.
        if ($id === '') {
            return $response;
        }

        // A session that did not change at all does not need to be sent to the browser
        if (! $session->hasChanged()) {
            return $response;
        }

        $response = $this->addSessionCookieHeaderToResponse($response, $id, $session);
        $response = $this->addCacheHeadersToResponse($response);

        return $response;
    }


    public function initializeId(SessionInterface $session): SessionInterface
    {
        $id = $session->getId();
        if ($id === '' || $session->isRegenerated()) {
            $session = new Session($session->toArray(), $this->generateRandomHash());
        }

        session_id($session->getId());

        return $session;
    }

    private function getSessionCookieValueFromRequest(ServerRequestInterface $request): string
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if ($authHeader !== '') {
            $token = explode(' ', $authHeader)[1] ?? null;

            if ($token !== null) {
                return $token;
            }
        }

        if ('' === $request->getHeaderLine('Cookie')) {
            return $request->getCookieParams()[$this->cookieName] ?? '';
        }

        return FigRequestCookies::get($request, $this->cookieName)->getValue() ?? '';
    }



    private function generateRandomHash(): string
    {
        return hash('sha512', microtime(true) . uniqid('', true) . rand());
    }

    private function regenerateSession(): string
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $id = $this->generateRandomHash();
        $this->startSession($id, [
            'use_strict_mode' => false,
        ]);
        return $id;
    }

    /**
     * @param array $options Additional options to pass to `session_start()`.
     */
    private function startSession(string $id, array $options = []): void
    {
        session_id($id);
        session_start([
                'use_cookies'      => false,
                'use_only_cookies' => true,
                'cache_limiter'    => '',
            ] + $options);
    }
}