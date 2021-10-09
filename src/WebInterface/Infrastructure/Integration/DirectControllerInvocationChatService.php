<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Integration;

use Gaming\Chat\Presentation\Http\ChatController;
use Gaming\WebInterface\Application\ChatService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DirectControllerInvocationChatService implements ChatService
{
    private ChatController $chatController;

    public function __construct(ChatController $chatController)
    {
        $this->chatController = $chatController;
    }

    public function writeMessage(string $chatId, string $authorId, string $message): array
    {
        return $this->sendRequest(
            'writeMessage',
            [
                'chatId' => $chatId,
            ],
            [
                'authorId' => $authorId,
                'message' => $message
            ]
        );
    }

    public function messages(string $chatId, string $authorId, int $offset, int $limit): array
    {
        return $this->sendRequest(
            'messages',
            [
                'chatId' => $chatId,
                'authorId' => $authorId,
                'offset' => $offset,
                'limit' => $limit
            ]
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

        $response = $this->chatController->$method(
            new Request($queryParameter, $postParameter)
        );
        assert($response instanceof Response);

        return json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}
