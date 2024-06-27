<?php

declare(strict_types=1);

namespace MezzioSecurity\Service\Authorization;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationService implements AuthorizationInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * @param string $role This is equal to the route name or route requested permission
     * @param ServerRequestInterface $request
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isGranted(string $role, ServerRequestInterface $request): bool
    {
        /** @var UserInterface|null $user */
        $user = $request->getAttribute(UserInterface::class);
        $isAdmin = $user->getDetail('admin');

        if ($isAdmin) {
            return true;
        }

        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        $routeName = $routeResult->getMatchedRouteName();
        $matchedRoute = $routeResult->getMatchedRoute();

        if (!$matchedRoute instanceof Route) {
            return false;
        }

        $requiredPermission = $matchedRoute->getOptions()[AuthorizationInterface::class]['permission'] ?? $routeName;

        if (strtoupper($role) === strtoupper($requiredPermission)) {
            return true;
        }

        // Check if the permission has owner suffix (modifier) that limits only the permission to the
        // owner of the resource.
        // In that case we need to perform a dynamic assertion that will determine the ownership of
        // the resource.
        if (strtoupper($requiredPermission ) . '_OWNER' === strtoupper($role)) {
            $ownershipAssertionClass = $matchedRoute
                ->getOptions()[AuthorizationInterface::class]['ownership'] ?? null;

            if ($ownershipAssertionClass === null) {
                return false;
            }

            if (!is_a($ownershipAssertionClass, OwnerShipAssertionInterface::class, true)) {
                throw new \RuntimeException('The ownership assertion class must implement OwnerShipAssertionInterface');
            }

            /** @var OwnerShipAssertionInterface $ownershipAssertionInstance */
            $ownershipAssertionInstance = $this->container->get($ownershipAssertionClass);
            return $ownershipAssertionInstance->isOwner($user, $request);
        }

        return false;
    }
}