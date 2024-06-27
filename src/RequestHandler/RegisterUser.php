<?php

declare(strict_types=1);

namespace MezzioSecurity\RequestHandler;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Hydrator\HydrationInterface;
use MezzioSecurity\Dto\UserDto;
use MezzioSecurity\Service\UserManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegisterUser implements \Psr\Http\Server\RequestHandlerInterface
{
    public function __construct(
        private readonly UserManager $manager,
        private readonly HydrationInterface $hydration,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $payload = json_decode($request->getBody()->getContents(), true);
        $dto = new UserDto();

        $this->hydration->hydrate($payload, $dto);

        return new JsonResponse($this->manager->registerNewUser($dto));
    }
}