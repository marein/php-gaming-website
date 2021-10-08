<?php
declare(strict_types=1);

namespace Gaming\Chat\Presentation\Http;

use Gaming\Chat\Application\Command\WriteMessageCommand;
use Gaming\Chat\Application\Query\MessagesQuery;
use Gaming\Common\Bus\Bus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ChatController
{
    /**
     * @var Bus
     */
    private Bus $commandBus;

    /**
     * @var Bus
     */
    private Bus $queryBus;

    /**
     * ChatController constructor.
     *
     * @param Bus $commandBus
     * @param Bus $queryBus
     */
    public function __construct(
        Bus $commandBus,
        Bus $queryBus
    ) {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function writeMessageAction(Request $request): JsonResponse
    {
        $chatId = (string)$request->query->get('chatId');

        $this->commandBus->handle(
            new WriteMessageCommand(
                $chatId,
                (string)$request->request->get('authorId'),
                (string)$request->request->get('message')
            )
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
            $this->queryBus->handle(
                new MessagesQuery(
                    (string)$request->query->get('chatId'),
                    (string)$request->query->get('authorId'),
                    (int)$request->query->get('offset'),
                    (int)$request->query->get('limit')
                )
            )
        );
    }
}
