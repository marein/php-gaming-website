<?php
declare(strict_types=1);

namespace Gambling\WebInterface\Infrastructure\Integration;

use Gambling\Chat\Presentation\Http\ChatController;
use Gambling\WebInterface\Application\ChatService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class DirectControllerInvocationChatService implements ChatService
{
    /**
     * @var ChatController
     */
    private $chatController;

    /**
     * DirectControllerInvocationChatService constructor.
     *
     * @param ChatController $chatController
     */
    public function __construct(ChatController $chatController)
    {
        $this->chatController = $chatController;
    }

    /**
     * @inheritdoc
     */
    public function writeMessage(string $chatId, string $authorId, string $message): array
    {
        return $this->sendRequest(
            'writeMessage',
            [
                'chatId' => $chatId,
            ],
            [
                'authorId' => $authorId,
                'message'  => $message
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function messages(string $chatId, string $authorId, int $offset, int $limit): array
    {
        return $this->sendRequest(
            'messages',
            [
                'chatId'   => $chatId,
                'authorId' => $authorId,
                'offset'   => $offset,
                'limit'    => $limit
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
        $response = $this->chatController->$method(
            new Request($queryParameter, $postParameter)
        );

        return json_decode($response->getContent(), true);
    }
}
