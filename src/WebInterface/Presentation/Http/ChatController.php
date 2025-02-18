<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Chat\Application\Command\WriteMessageCommand;
use Gaming\Chat\Application\Query\MessagesQuery;
use Gaming\Common\Bus\Bus;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
                $this->security->getUser()->getUserIdentifier(),
                (string)$request->request->get('message')
            )
        );

        return new JsonResponse();
    }

    public function messagesAction(Request $request, string $chatId): JsonResponse
    {
        return new JsonResponse(
            [
                'messages' => $this->chatQueryBus->handle(
                    new MessagesQuery(
                        $chatId,
                        $this->security->tryUser()->getUserIdentifier(),
                        0,
                        10000
                    )
                )
            ]
        );
    }
}
