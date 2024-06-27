<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Integration\RequestHandler;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use MezzioSecurity\Entity\User;
use MezzioSecurity\RequestHandler\DeleteUser;
use MezzioSecurity\Test\Util\ContainerInitTrait;
use MezzioSecurity\Test\Util\DatabaseInitTrait;
use MezzioSecurity\Test\Util\MockStreamTrait;
use MezzioSecurity\Test\Util\UserInitTrait;
use PHPUnit\Framework\TestCase;

class DeleteUserHandlerTest extends TestCase
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

    public function testDeleteUser(): void
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('id', 1);
        /** @var DeleteUser $handler */
        $handler = $this->container->get(DeleteUser::class);

        $response = $handler->handle($request);
        self::assertJsonStringEqualsJsonString(
            '{"success": true}',
            $response->getBody()->getContents(),
        );

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(User::class)->find(1);
        self::assertNull($user);
    }
}