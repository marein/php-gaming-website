<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Integration;

use Gaming\Identity\Port\Adapter\Http\UserController;
use Gaming\WebInterface\Application\IdentityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DirectControllerInvocationIdentityService implements IdentityService
{
    private UserController $userController;

    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    public function arrive(): array
    {
        return $this->sendRequest('arrive');
    }

    public function signUp(string $userId, string $email, string $username, bool $dryRun = false): array
    {
        return $this->sendRequest(
            'signUp',
            ['userId' => $userId],
            ['email' => $email, 'username' => $username, 'dryRun' => $dryRun]
        );
    }

    /**
     * @param array<string, mixed> $queryParameter
     * @param array<string, mixed> $postParameter
     *
     * @return array<string, mixed>
     */
    private function sendRequest(string $actionName, array $queryParameter = [], array $postParameter = []): array
    {
        $method = $actionName . 'Action';

        $response = $this->userController->$method(
            new Request($queryParameter, $postParameter)
        );
        assert($response instanceof Response);

        return json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}
