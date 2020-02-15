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
        $chatId = $request->query->get('chatId');

        $this->commandBus->handle(
            new WriteMessageCommand(
                $chatId,
                $request->request->get('authorId'),
                $request->request->get('message')
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
                    $request->query->get('chatId'),
                    $request->query->get('authorId'),
                    (int)$request->query->get('offset'),
                    (int)$request->query->get('limit')
                )
            )
        );
    }
}
