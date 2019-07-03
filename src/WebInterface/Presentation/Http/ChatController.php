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
     * @param string  $chatId
     *
     * @return JsonResponse
     */
    public function writeMessageAction(Request $request, string $chatId): JsonResponse
    {
        return new JsonResponse(
            $this->chatService->writeMessage(
                $chatId,
                $request->getSession()->get('user'),
                $request->request->get('message')
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
        return new JsonResponse(
            $this->chatService->messages(
                $chatId,
                $request->getSession()->get('user'),
                0,
                10000
            )
        );
    }
}
