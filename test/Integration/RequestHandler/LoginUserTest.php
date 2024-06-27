<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Integration\RequestHandler;

use Carbon\Carbon;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\Session;
use Mezzio\Session\SessionMiddleware;
use MezzioSecurity\RequestHandler\LoginUser;
use MezzioSecurity\RequestHandler\View\Login;
use MezzioSecurity\Test\Util\ContainerInitTrait;
use MezzioSecurity\Test\Util\DatabaseInitTrait;
use MezzioSecurity\Test\Util\MockStreamTrait;
use MezzioSecurity\Test\Util\UserInitTrait;
use PHPUnit\Framework\TestCase;

class LoginUserTest extends TestCase
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
        $session = new Session([]);
        $request = new ServerRequest();
        $request = $request->withAttribute(
            SessionMiddleware::SESSION_ATTRIBUTE,
            $session,
        )->withParsedBody([
            'username' => self::USERNAME,
            'password' => self::PWD,
        ])->withMethod('POST');
        /** @var Login $handler */
        $handler = $this->container->get(LoginUser::class);
        $response = $handler->handle($request);
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertJsonStringEqualsJsonString(
            '{"success": true, "redirect": "/"}',
            $response->getBody()->getContents()
        );
        self::assertTrue($session->has(UserInterface::class));
    }
}