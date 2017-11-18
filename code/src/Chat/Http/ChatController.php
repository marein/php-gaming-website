<?php

namespace Gambling\Chat\Http;

use Gambling\Chat\Model\ChatGateway;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ChatController
{
    /**
     * @var ChatGateway
     */
    private $chatGateway;

    /**
     * ChatController constructor.
     *
     * @param ChatGateway $chatGateway
     */
    public function __construct(ChatGateway $chatGateway)
    {
        $this->chatGateway = $chatGateway;
    }

    public function writeMessageAction(Request $request): JsonResponse
    {
        $chatId = $request->query->get('chatId');

        $this->chatGateway->writeMessage(
            $chatId,
            $request->request->get('authorId'),
            $request->request->get('message')
        );

        return new JsonResponse([
            'chatId' => $chatId
        ]);
    }

    public function messagesAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->chatGateway->messagesByChat(
                $request->query->get('chatId'),
                $request->query->get('authorId'),
                (int)$request->query->get('offset'),
                (int)$request->query->get('limit')
            )
        );
    }
}
