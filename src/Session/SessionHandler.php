<?php

declare(strict_types=1);

namespace MezzioSecurity\Session;

use Doctrine\ORM\EntityManagerInterface;
use MezzioSecurity\Entity\Session;
use SessionUpdateTimestampHandlerInterface;

class SessionHandler implements \SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function close(): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        try {
            /** @var Session|null $session */
            $session = $this->entityManager->getRepository(Session::class)->find($id);

            if ($session === null) {
                return true;
            }

            $this->entityManager->remove($session);
            $this->entityManager->flush();
        } catch (\Throwable $throwable) {
            return false;
        }

        return true;
    }

    public function gc(int $maxLifetime): int|false
    {
        // Calculate what is to be deemed old
        $old = time() - $maxLifetime;
        try {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('s')->from('Security\Model\Session', 's')->where('s.access < :old');
            $qb->setParameter('old', $old);

            /** @var Session[] $sessions */
            $sessions = $qb->getQuery()->getResult();

            $count = count($sessions);
            foreach ($sessions as $session) {
                $this->entityManager->remove($session);
            }

            $this->entityManager->flush();
        } catch (\Throwable $throwable) {
            return false;
        }

        return $count;
    }

    public function open(string $path, string $name): bool
    {
        return  true;
    }

    public function read(string $id): string|false
    {
        /** @var Session|null $session */
        $session = $this->entityManager->getRepository(Session::class)->find($id);

        if ($session === null) {
            return '';
        }

        return $session->getData();
    }

    public function write(string $id, string $data): bool
    {
        try {
            /** @var Session|null $session */
            $session = $this->entityManager->getRepository(Session::class)->find($id);

            if ($session === null) {
                $session = new Session();
                $session->setId($id);
            }

            $access = time();
            $session->setAccess($access);
            $session->setData($data);
            $this->entityManager->persist($session);
            $this->entityManager->flush();
        } catch (\Throwable $throwable) {
            return false;
        }

        return true;
    }

    public function validateId(string $id): bool
    {
        /** @var Session|null $session */
        $session = $this->entityManager->getRepository(Session::class)->find($id);

        if ($session === null) {
            return false;
        }

        return true;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        /** @var Session|null $session */
        $session = $this->entityManager->getRepository(Session::class)->find($id);

        if ($session === null) {
            return false;
        }

        $session->setAccess(time());
        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return true;
    }
}