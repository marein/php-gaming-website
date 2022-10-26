<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ChatService;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ChatController
{
    public function __construct(
        private readonly ChatService $chatService,
        private readonly Security $security
    ) {
    }

    public function writeMessageAction(Request $request, string $chatId): JsonResponse
    {
        return new JsonResponse(
            $this->chatService->writeMessage(
                $chatId,
                $this->security->getUser()->getUserIdentifier(),
                (string)$request->request->get('message')
            )
        );
    }

    public function messagesAction(Request $request, string $chatId): JsonResponse
    {
        return new JsonResponse(
            [
                'messages' => $this->chatService->messages(
                    $chatId,
                    $this->security->getUser()->getUserIdentifier(),
                    0,
                    10000
                )
            ]
        );
    }
}
