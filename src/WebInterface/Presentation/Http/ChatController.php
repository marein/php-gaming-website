<?php
declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ChatService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ChatController
{
    /**
     * @var ChatService
     */
    private ChatService $chatService;

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
     * @param string  $chatId
     *
     * @return JsonResponse
     */
    public function writeMessageAction(Request $request, string $chatId): JsonResponse
    {
        return new JsonResponse(
            $this->chatService->writeMessage(
                $chatId,
                (string)$request->getSession()->get('user'),
                (string)$request->request->get('message')
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $chatId
     *
     * @return JsonResponse
     */
    public function messagesAction(Request $request, string $chatId): JsonResponse
    {
        return new JsonResponse([
            'messages' => $this->chatService->messages(
                $chatId,
                (string)$request->getSession()->get('user'),
                0,
                10000
            )
        ]);
    }
}
