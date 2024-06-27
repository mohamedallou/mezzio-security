<?php

declare(strict_types=1);

namespace MezzioSecurity\RequestHandler;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Hydrator\HydrationInterface;
use MezzioSecurity\Dto\UserDto;
use MezzioSecurity\Service\UserManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UpdateUser implements RequestHandlerInterface
{
    public function __construct(
        private readonly UserManager $manager,
        private readonly HydrationInterface $hydration,
    ) {
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

        $payload = json_decode($request->getBody()->getContents(), true);
        $dto = new UserDto();
        $this->hydration->hydrate($payload, $dto);

        return new JsonResponse($this->manager->updateUser($dto, (int) $userId));
    }
}