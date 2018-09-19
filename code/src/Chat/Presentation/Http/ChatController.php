<?php
declare(strict_types=1);

namespace Gambling\Chat\Presentation\Http;

use Gambling\Chat\Application\ChatService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ChatController
{
    /**
     * @var ChatService
     */
    private $chatService;

    /**
     * ChatController constructor.
     *
     * @param ChatService $chatService
     */
    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function writeMessageAction(Request $request): JsonResponse
    {
        $chatId = $request->query->get('chatId');

        $this->chatService->writeMessage(
            $chatId,
            $request->request->get('authorId'),
            $request->request->get('message')
        );

        return new JsonResponse([
            'chatId' => $chatId
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function messagesAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->chatService->messages(
                $request->query->get('chatId'),
                $request->query->get('authorId'),
                (int)$request->query->get('offset'),
                (int)$request->query->get('limit')
            )
        );
    }
}
