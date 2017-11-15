<?php

namespace Gambling\WebInterface\Infrastructure\Integration;

use Gambling\User\Port\Adapter\Http\UserController;
use Gambling\WebInterface\Application\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class DirectControllerInvocationUserService implements UserService
{
    /**
     * @var UserController
     */
    private $userController;

    /**
     * DirectControllerInvocationUserService constructor.
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
    public function signUp(string $username, string $password): array
    {
        return $this->sendRequest(
            'signUp',
            [],
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

        /** @var JsonResponse $response */
        $response = $this->userController->$method(
            new Request($queryParameter, $postParameter)
        );

        return json_decode($response->getContent(), true);
    }
}
