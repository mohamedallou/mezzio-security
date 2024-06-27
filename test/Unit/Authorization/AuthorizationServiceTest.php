<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Unit\Authorization;

use Laminas\Diactoros\ServerRequest;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\MiddlewareContainer;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use MezzioSecurity\Service\Authorization\AuthorizationService;
use MezzioSecurity\Service\Authorization\OwnerShipAssertionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AuthorizationServiceTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testIsGranted(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $ownershipAssertion = $this->createMock(OwnerShipAssertionInterface::class);
        $ownershipAssertion->method('isOwner')->willReturn(true);
        $container->method('get')->willReturn($ownershipAssertion);

        $subject = new AuthorizationService($container);
        $user = new DefaultUser(
            'test',
            ['test.route'],
            [
                'admin' => true,
            ]
        );
        $request = $this->generateRequestWithUser($user);

        self::assertTrue($subject->isGranted('test.route', $request));

        // User ist not admin and does not contain the required permission
        $user = $user = new DefaultUser(
            'test',
            ['test.route'],
            [
                'admin' => false,
            ]
        );
        $request = $this->generateRequestWithUser($user);
        self::assertFalse($subject->isGranted('test.route', $request));

        // User ist not admin and contains the required permission
        $user = $user = new DefaultUser(
            'test',
            ['ping'],
            [
                'admin' => false,
            ]
        );
        $request = $this->generateRequestWithUser($user);
        self::assertTrue($subject->isGranted('ping', $request));

        // Try with ownership is true
        self::assertTrue($subject->isGranted('ping_OWNER', $request));
    }

    private function generateRequestWithUser(UserInterface $user): ServerRequest
    {
        $container = $this->createMock(ContainerInterface::class);
        $middlewareFactory = new MiddlewareFactory(
            new MiddlewareContainer($container),
        );
        $route = new Route(
            '/api/ping',
            $middlewareFactory->prepare(function (ServerRequest $request) {
                return true;
            }),
            ['GET', 'POST'],
            'ping'
        );

        $routeResult = RouteResult::fromRoute($route);

        $route->setOptions([
            AuthorizationInterface::class => [
                'ownership' => OwnershipAssertionFake::class,
            ]
        ]);
        return (new ServerRequest())
            ->withAttribute(
                UserInterface::class,
                $user,
            )->withAttribute(
                RouteResult::class,
                $routeResult,
            );
    }
}