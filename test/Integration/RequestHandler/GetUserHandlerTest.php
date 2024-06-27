<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Integration\RequestHandler;

use Carbon\Carbon;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use MezzioSecurity\RequestHandler\GetUser;
use MezzioSecurity\Test\Util\ContainerInitTrait;
use MezzioSecurity\Test\Util\DatabaseInitTrait;
use MezzioSecurity\Test\Util\UserInitTrait;
use PHPUnit\Framework\TestCase;

class GetUserHandlerTest extends TestCase
{
    use DatabaseInitTrait;
    use ContainerInitTrait;
    use UserInitTrait;

    private const USERNAME = 'test1234';
    private const EMAIL = 'test@mail.com';
    private const PWD = 'pwd12345678';

    private const USERNAME_2 = 'test12345';
    private const EMAIL_2 = 'test2@mail.com';
    private const PWD_2 = 'pwd123456789';

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
        $this->insertNewUser(
            self::USERNAME_2,
            self::EMAIL_2,
            self::PWD_2,
        );
    }

    public function testGetUser(): void
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('id', 1);
        /** @var GetUser $handler */
        $handler = $this->container->get(GetUser::class);
        $response = $handler->handle($request);
        self::assertJsonStringEqualsJsonString(
            '{"id":1,"email":"test@mail.com","username":"test1234","admin":false,"firstName":null,"lastName":null,"permissions":[],"created":"2000-01-01 00:00:00","updated":"2000-01-01 00:00:00"}',
            $response->getBody()->getContents(),
        );
    }

    public function testGetUsers(): void
    {
        $request = new ServerRequest();
        /** @var GetUser $handler */
        $handler = $this->container->get(GetUser::class);
        $response = $handler->handle($request);
        $request = $request->withQueryParams(['page' => 1]);
        self::assertJsonStringEqualsJsonString(
            '[
                {"id":1,"email":"test@mail.com","username":"test1234","admin":false,"firstName":null,"lastName":null,"permissions":[],"created":"2000-01-01 00:00:00","updated":"2000-01-01 00:00:00"},
                {"id":2,"email":"test2@mail.com","username":"test12345","admin":false,"firstName":null,"lastName":null,"permissions":[],"created":"2000-01-01 00:00:00","updated":"2000-01-01 00:00:00"}
             ]',
            $response->getBody()->getContents(),
        );

        // Test pagination, we only have 2 users, empty result is expected
        $request = $request->withQueryParams(['page' => 2]);
        $response = $handler->handle($request);

        self::assertJsonStringEqualsJsonString(
            '[]',
            $response->getBody()->getContents(),
        );
    }
}