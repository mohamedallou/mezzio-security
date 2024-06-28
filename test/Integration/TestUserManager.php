<?php

declare(strict_types=1);

namespace MezzioSecurity\Test\Integration;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\ServiceManager;
use MezzioSecurity\Dto\UserDto;
use MezzioSecurity\Entity\User;
use MezzioSecurity\Event\UserRegisteredEvent;
use MezzioSecurity\Event\UserUpdatedEvent;
use MezzioSecurity\Service\UserManager;
use MezzioSecurity\Test\Util\DatabaseInitTrait;
use PHPUnit\Framework\TestCase;
use MezzioSecurity\ConfigProvider;

class TestUserManager extends TestCase
{
    use DatabaseInitTrait;

    private const USERNAME = 'test1234';
    private const EMAIL = 'test@mail.com';
    private const PWD = 'pwd12345678';
    private ServiceManager $container;

    /**
     * @return User
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function insertNewUser(): User
    {
        $userDto = new UserDto();
        $userDto->username = self::USERNAME; // min 5 characters
        $userDto->email = self::EMAIL;
        $userDto->password = self::PWD; // min 8 characters

        /** @var UserManager $userManager */
        $userManager = $this->container->get(UserManager::class);

        $user = $userManager->registerNewUser($userDto);
        return $user;
    }

    protected function setUp(): void
    {
        $this->container = $this->getContainer();
    }

    public function testRegisterNewUser(): void
    {
        $user = $this->insertNewUser();
        self::assertEquals(self::USERNAME, $user->getUsername());
        self::assertEquals(self::EMAIL, $user->getEmail());
        self::assertTrue(password_verify(self::PWD,$user->getPassword()));
        self::assertEquals(1, $user->getId());
        self::assertNotEmpty($user->getUserDoiHash());

        // Try to register the same user again, an exception will occur becasue of duplicate data
        $this->expectException(UniqueConstraintViolationException::class);
        $this->insertNewUser();
    }

    public function testUpdateNewUser(): void
    {
        $user = $this->insertNewUser();
        $userDto = new UserDto();
        $userDto->firstName = 'Foo';
        /** @var UserManager $userManager */
        $userManager = $this->container->get(UserManager::class);
        $userManager->updateUser($userDto, $user->getId());

        self::assertEquals('Foo', $user->getFirstName());
    }

    public function testAuthenticate(): void
    {
        $this->insertNewUser();
        /** @var UserManager $userManager */
        $userManager = $this->container->get(UserManager::class);
        $user = $userManager->authenticate(self::USERNAME, self::PWD);

        self::assertEquals(self::USERNAME, $user?->getIdentity() ?? '');
    }

    public function testAssignPermissions(): void
    {
        $user = $this->insertNewUser();
        /** @var UserManager $userManager */
        $userManager = $this->container->get(UserManager::class);
        $userManager->assignPermissions($user->getId(), ['profile.edit']);
        self::assertEquals(['profile.edit'], $user->getPermissions());
    }

    private function getContainer(): ServiceManager
    {
        $configAggregator = new ConfigAggregator([
            \Laminas\InputFilter\ConfigProvider::class,
            new ArrayProvider(
                [
                    'dependencies' => [
                        'factories' => [
                            EventManagerInterface::class => function($container) {
                                $evm = new EventManager();
                                $evm->attach(
                                    UserRegisteredEvent::class,
                                    function (UserRegisteredEvent $event) {
                                        self::assertEquals('test@mail.com', $event->getTarget()->getEmail());
                                        self::assertEquals('test1234', $event->getTarget()->getUsername());
                                        self::assertNotEmpty($event->getTarget()->getUserDoiHash());
                                    }
                                );

                                $evm->attach(
                                    UserUpdatedEvent::class,
                                    function (UserUpdatedEvent $event) {
                                        self::assertEquals('Foo', $event->getTarget()->getFirstName());
                                    }
                                );

                                return $evm;
                            },
                            EntityManagerInterface::class => function ($container) {
                                return $this->initDatabase();
                            },
                        ]
                    ]
                ]
            ),
            ConfigProvider::class,
        ]);

        $config = $configAggregator->getMergedConfig();
        $dependencies                       = $config['dependencies'];
        $dependencies['services']['config'] = $config;
        return new ServiceManager($dependencies);
    }
}