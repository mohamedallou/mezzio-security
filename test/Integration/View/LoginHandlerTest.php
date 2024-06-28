<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Integration\View;

use Carbon\Carbon;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Authentication\UserInterface;
use Mezzio\MiddlewareFactoryInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionMiddleware;
use MezzioSecurity\RequestHandler\View\Login;
use MezzioSecurity\Test\Util\ContainerInitTrait;
use MezzioSecurity\Test\Util\DatabaseInitTrait;
use MezzioSecurity\Test\Util\MockStreamTrait;
use MezzioSecurity\Test\Util\UserInitTrait;
use PHPUnit\Framework\TestCase;

class LoginHandlerTest extends TestCase
{
    use DatabaseInitTrait;
    use ContainerInitTrait;
    use UserInitTrait;
    use MockStreamTrait;

    private const USERNAME = 'test1234';
    private const EMAIL = 'test@mail.com';
    private const PWD = 'pwd12345678';

    private ServiceManager $container;

    protected function setUp(): void
    {
        Carbon::setTestNow(Carbon::create(
            2000,
            1,
            1,
            0,
            0,
            0,
        ));
        $this->container = $this->getContainer();
        $this->insertNewUser(
            self::USERNAME,
            self::EMAIL,
            self::PWD
        );
    }

    public function testSuccessfulLogin(): void
    {
        $session = new \Mezzio\Session\Session([]);
        $request = new ServerRequest();
        $request = $request->withAttribute(
            SessionMiddleware::SESSION_ATTRIBUTE,
            $session,
        )->withParsedBody([
            'username' => self::USERNAME,
            'password' => self::PWD,
        ])->withMethod('POST');
        /** @var Login $handler */
        $handler = $this->container->get(Login::class);
        $response = $handler->handle($request);
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertTrue($session->has(UserInterface::class));
    }

    public function testLoginForm(): void
    {
        /** @var MiddlewareFactoryInterface $factory */
        $factory = $this->container->get(MiddlewareFactoryInterface::class);
        /** @var RouterInterface $router */
        $router = $this->container->get(RouterInterface::class);
        $router->addRoute( new Route(
            '/login',
            $factory->prepare(Login::class),
            ['GET', 'POST'],
            'login'
        ));
        $request = new ServerRequest();
        $request = $request->withAttribute(
            SessionMiddleware::SESSION_ATTRIBUTE,
            new \Mezzio\Session\Session([]),
        )->withMethod('GET');
        /** @var Login $handler */
        $handler = $this->container->get(Login::class);
        $response = $handler->handle($request);
        self::assertStringEqualsFile(
            __DIR__ . '/Result/login.html',
            $response->getBody()->getContents()
        );
    }
}