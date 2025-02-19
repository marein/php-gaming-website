<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Chat\Application\Command\WriteMessageCommand;
use Gaming\Chat\Application\Query\MessagesQuery;
use Gaming\Common\Bus\Bus;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Gaming\WebInterface\Infrastructure\Security\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ChatController
{
    public function __construct(
        private readonly Bus $chatCommandBus,
        private readonly Bus $chatQueryBus,
        private readonly Security $security
    ) {
    }

    public function writeMessageAction(Request $request, string $chatId): JsonResponse
    {
        $this->chatCommandBus->handle(
            new WriteMessageCommand(
                $chatId,
                $this->security->forceUser()->getUserIdentifier(),
                (string)$request->request->get('message')
            )
        );

        return new JsonResponse();
    }

    public function messagesAction(#[CurrentUser] ?User $user, string $chatId): JsonResponse
    {
        return new JsonResponse(
            [
                'messages' => $this->chatQueryBus->handle(
                    new MessagesQuery(
                        $chatId,
                        $user?->getUserIdentifier() ?? '',
                        0,
                        10000
                    )
                )
            ]
        );
    }
}
