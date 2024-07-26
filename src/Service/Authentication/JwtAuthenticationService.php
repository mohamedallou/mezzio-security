<?php

declare(strict_types=1);

namespace MezzioSecurity\Service\Authentication;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class JwtAuthenticationService implements AuthenticationInterface
{
    private const ALGORITHM = 'HS256';

    public function __construct(
        private readonly string $secretKey,
        private readonly UserRepositoryInterface $repository,
    ) {
    }

    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $authHeaderParts = explode(' ', $authHeader);
        $authScheme = $authHeaderParts[0] ?? '';

        if ($authScheme !== '' && $authScheme !== 'Bearer') {
            return null;
        }

        $jwt = $authHeaderParts[1] ?? $request->getQueryParams()['jwt'] ?? null;

        if ($jwt !== null) {
            $key = $this->secretKey;
            if ($key === '') {
                throw  new \Exception('Key not defined');
            }

            try {
                $decoded = JWT::decode($jwt, new Key($key, self::ALGORITHM));
            } catch (\Throwable $exception) {
                // On validation failure (signature, expiration date) an exception will be thrown
                return null;
            }

            // Permissions are equal to roles in our system
            $roles = $decoded->permissions;
            $identity = $decoded->identity;
            $details = $decoded->details;

            return new DefaultUser($identity, $roles, $details);
        }

        if ('POST' !== strtoupper($request->getMethod())) {
            return null;
        }

        $params   = $request->getParsedBody();
        $username = $this->config['username'] ?? 'username';
        $password = $this->config['password'] ?? 'password';
        if (! isset($params[$username]) || ! isset($params[$password])) {
            return null;
        }

        $user = $this->repository->authenticate(
            $params[$username],
            $params[$password]
        );

        if (null !== $user) {
            // add jwt token to user details.
            $details = $user->getDetails();
            $details['token'] = JWT::encode(
                [
                    'identity' => $user->getIdentity(),
                    'permissions' => $user->getRoles(),
                    'details' => $user->getDetails(),
                    'exp' => (new \DateTimeImmutable())->modify('+ 1 day')->getTimestamp(),
                    'nbf' => (new \DateTimeImmutable())->getTimestamp(),
                    'iat' => (new \DateTimeImmutable())->getTimestamp(),
                ],
                $this->secretKey,
                self::ALGORITHM
            );
            return new DefaultUser(
                $user->getIdentity(),
                $user->getRoles(),
                $details,
            );
        }

        return null;
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

        return new RedirectResponse('/login');
    }
}