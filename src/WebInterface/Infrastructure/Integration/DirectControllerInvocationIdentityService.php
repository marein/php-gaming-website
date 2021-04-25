<?php
declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Integration;

use Gaming\Identity\Port\Adapter\Http\UserController;
use Gaming\WebInterface\Application\IdentityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DirectControllerInvocationIdentityService implements IdentityService
{
    /**
     * @var UserController
     */
    private UserController $userController;

    /**
     * DirectControllerInvocationIdentityService constructor.
     *
     * @param UserController $userController
     */
    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    /**
     * @inheritdoc
     */
    public function arrive(): array
    {
        return $this->sendRequest('arrive');
    }

    /**
     * @inheritdoc
     */
    public function signUp(string $userId, string $username, string $password): array
    {
        return $this->sendRequest(
            'signUp',
            [
                'userId' => $userId
            ],
            [
                'username' => $username,
                'password' => $password
            ]
        );
    }

    /**
     * Make a call to the controller.
     *
     * @param string $actionName
     * @param array  $queryParameter
     * @param array  $postParameter
     *
     * @return array
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
