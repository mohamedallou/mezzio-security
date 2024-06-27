<?php

declare(strict_types=1);

namespace MezzioSecurity\RequestHandler;

use Laminas\Diactoros\Response\JsonResponse;
use MezzioSecurity\Service\UserManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DeleteUser implements RequestHandlerInterface
{
    public function __construct(private readonly UserManager $manager)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userId = $request->getAttribute('id');

        if ($userId === null) {
            return new JsonResponse(
                [
                    'errors' => 'Missing user id',
                ],
                400);
        }

        $this->manager->deleteUser((int) $userId);
        return new JsonResponse(['success' => true,]);
    }
}