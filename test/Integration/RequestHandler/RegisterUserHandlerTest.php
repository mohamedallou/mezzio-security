<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Integration\RequestHandler;

use Carbon\Carbon;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use MezzioSecurity\RequestHandler\RegisterUser;
use MezzioSecurity\Test\Util\ContainerInitTrait;
use MezzioSecurity\Test\Util\DatabaseInitTrait;
use MezzioSecurity\Test\Util\MockStreamTrait;
use MezzioSecurity\Test\Util\UserInitTrait;
use PHPUnit\Framework\TestCase;

class RegisterUserHandlerTest extends TestCase
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
    }

    public function testRegisterUser(): void
    {
        $request = new ServerRequest();
        $data = [
            'username' => self::USERNAME,
            'password' => self::PWD,
            'email' => self::EMAIL,
        ];
        $stream = $this->createStream($data);
        $request = $request->withBody($stream);
        /** @var RegisterUser $handler */
        $handler = $this->container->get(RegisterUser::class);

        $response = $handler->handle($request);
        self::assertJsonStringEqualsJsonString(
            '{"id":1,"email":"test@mail.com","username":"test1234","admin":false,"firstName":null,"lastName":null,"permissions":[],"created":"2000-01-01 00:00:00","updated":"2000-01-01 00:00:00"}',
            $response->getBody()->getContents(),
        );
    }
}