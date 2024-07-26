<?php

declare(strict_types=1);

namespace MezzioSecurity\Repository;

use MezzioSecurity\Entity\User;
use MezzioSecurity\Entity\UserInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<User>
 */
class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    public function refreshAccessTimeForUser(UserInterface $user): void
    {
        $user->refreshAccessTime();
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function activateUserByToken(string $doiToken): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        $userEntity = $userRepository->findOneBy(['userDoiHash' => $doiToken]);
        if ($userEntity === null) {
            return;
        }

        $userEntity->activate();
        $this->getEntityManager()->persist($userEntity);
        $this->getEntityManager()->flush();
    }
}
