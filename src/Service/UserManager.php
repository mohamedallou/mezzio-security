<?php

namespace MezzioSecurity\Service;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Hydrator\ExtractionInterface;
use Laminas\Hydrator\HydrationInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface as MezzioUserRepoInterface;
use MezzioSecurity\Dto\UserDto;
use MezzioSecurity\Entity\User;
use MezzioSecurity\Event\UserRegisteredEvent;
use MezzioSecurity\Event\UserUpdatedEvent;
use MezzioSecurity\Exception\FailedLoginException;
use MezzioSecurity\Exception\InvalidUserDataException;
use MezzioSecurity\Exception\UserNotFoundException;
use MezzioSecurity\Repository\UserRepository;
use MezzioSecurity\Repository\UserRepositoryInterface;

class UserManager implements MezzioUserRepoInterface
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param EventManagerInterface $eventManager
     * @param InputFilterPluginManager<InputFilterInterface<array<string, mixed>>> $inputFilterPluginManager
     * @param ExtractionInterface&HydrationInterface $hydrator
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventManagerInterface  $eventManager,
        private readonly InputFilterPluginManager $inputFilterPluginManager,
        private readonly ExtractionInterface&HydrationInterface $hydrator,
    ) {
    }

    private function generateRandomHash(): string
    {
        return hash('sha256', microtime(true) . uniqid('', true) . rand());
    }

    /**
     * @throws \Exception
     */
    public function registerNewUser(UserDto $dto): User
    {
        $data = $this->hydrator->extract($dto);
        $inputFilter = $this->inputFilterPluginManager->get(UserDto::class. '_register');
        if (!$inputFilter instanceof InputFilterInterface) {
            throw new \RuntimeException('No input filter was found');
        }

        $inputFilter->setValidationGroup(['email', 'username', 'password']);
        $inputFilter->setData($data);
        if (!$inputFilter->isValid()) {
            $errors = $inputFilter->getMessages();
            throw new InvalidUserDataException('Invalid user data', $errors);
        }

        // Get a valid and filtered dto
        $dto = $this->hydrator->hydrate($inputFilter->getValues(), new UserDto());
        assert($dto instanceof UserDto);
        $user = new User();
        $user->fillFromDto($dto);
        $doiHash = $this->generateRandomHash();
        $user->setUserDoiHash($doiHash);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->eventManager->triggerEvent(new UserRegisteredEvent($user));
        return $user;
    }

    /**
     * @param UserDto $dto
     * @param int $userId
     * @return User
     */
    public function updateUser(UserDto $dto, int $userId): User
    {
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        if ($user === null) {
            throw new UserNotFoundException(
                sprintf('User with id %d was not found', $userId)
            );
        }

        $data = $this->hydrator->extract($dto);
        $data = array_filter(
            $data,
            fn ($el): bool => $el !== null
        );
        $inputFilter = $this->inputFilterPluginManager->get(UserDto::class);

        if (!$inputFilter instanceof InputFilterInterface) {
            throw new \RuntimeException('No input filter was found');
        }

        $inputFilter->setData($data);

        if (!$inputFilter->isValid()) {
            $errors = $inputFilter->getMessages();
            throw new InvalidUserDataException('Invalid user data', $errors);
        }

        // Get a valid and filtered dto
        $dto = $this->hydrator->hydrate($inputFilter->getValues(), new UserDto());
        assert($dto instanceof UserDto);
        $user->fillFromDto($dto);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->eventManager->triggerEvent(new UserUpdatedEvent($user));

        return $user;
    }

    public function authenticate(string $credential, ?string $password = null): ?UserInterface
    {
        //TODO: check  active state
        /** @var UserRepositoryInterface $userRepo */
        $userRepo = $this->entityManager->getRepository(User::class);
        // try username
        /** @var User|null $user */
        $user = $userRepo->findOneBy(['username' => $credential]);
        if ($user === null) {
            return null;
        }

        $userRepo->refreshAccessTimeForUser($user);

        if ($password === null) {
            //todo: implement jwt token authentication (api token)
            throw new FailedLoginException('Passwordless authenitcation is not supported');
        }

        if (!password_verify($password, $user->getPassword())) {
            return null;
        }

        $permissions = $user->getPermissions();
        if ($user->isAdmin()) {
            $permissions[] = 'admin';
        }

        return new DefaultUser($user->getUsername(), $permissions, $user->getDetails());
    }

    public function deleteUser(int $userId): void
    {
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        if ($user === null) {
            throw new UserNotFoundException(
                sprintf('User with id %d was not found', $userId)
            );
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * @param int $userId
     * @param string[] $permissions
     * @return void
     */
    public function assignPermissions(int $userId, array $permissions): void
    {
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        if ($user === null) {
            throw new UserNotFoundException(
                sprintf('User with id %d was not found', $userId)
            );
        }

        $user->clearPermissions();

        foreach ($permissions as $permission) {
            $user->addPermission($permission);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function fetchUser(int $userId): User
    {
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        if ($user === null) {
            throw new UserNotFoundException(
                sprintf('User with id %d was not found', $userId)
            );
        }

        return $user;
    }

    /**
     * @param int $pageNumber
     * @param int $pageSize
     * @return User[]
     */
    public function fetchUsers(int $pageNumber, int $pageSize = 10): array
    {
        $offset = $pageSize * max(($pageNumber - 1), 0);
        /** @var User[] $users */
        $users = $this->entityManager->getRepository(User::class)
            ->findBy(
                [],
                limit: $pageSize,
                offset: $offset,
            );

        return $users;
    }
}
