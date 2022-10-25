<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ChatService;
use Gaming\WebInterface\Infrastructure\Security\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ChatController
{
    public function __construct(
        private readonly ChatService $chatService
    ) {
    }

    public function writeMessageAction(Request $request, User $user, string $chatId): JsonResponse
    {
        return new JsonResponse(
            $this->chatService->writeMessage(
                $chatId,
                $user->getUserIdentifier(),
                (string)$request->request->get('message')
            )
        );
    }

    public function messagesAction(Request $request, User $user, string $chatId): JsonResponse
    {
        return new JsonResponse(
            [
                'messages' => $this->chatService->messages(
                    $chatId,
                    $user->getUserIdentifier(),
                    0,
                    10000
                )
            ]
        );
    }
}
