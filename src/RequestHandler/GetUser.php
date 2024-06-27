<?php

declare(strict_types=1);

namespace MezzioSecurity\RequestHandler;

use Laminas\Diactoros\Response\JsonResponse;
use MezzioSecurity\Service\UserManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetUser implements RequestHandlerInterface
{
    public function __construct(private readonly UserManager $manager)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        if ($id > 0) {
            $user = $this->manager->fetchUser($id);
            return new JsonResponse($user);
        }

        $page = (int) ($request->getQueryParams()['page'] ?? 1);
        $users = $this->manager->fetchUsers($page);
        return new JsonResponse($users);
    }
}