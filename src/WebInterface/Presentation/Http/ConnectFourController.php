<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Command\AbortCommand;
use Gaming\ConnectFour\Application\Game\Command\JoinCommand;
use Gaming\ConnectFour\Application\Game\Command\MoveCommand;
use Gaming\ConnectFour\Application\Game\Command\OpenCommand;
use Gaming\ConnectFour\Application\Game\Command\ResignCommand;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ConnectFourController
{
    public function __construct(
        private readonly Bus $connectFourCommandBus,
        private readonly Security $security
    ) {
    }

    public function openAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            [
                'gameId' => $this->connectFourCommandBus->handle(
                    new OpenCommand($this->security->getUser()->getUserIdentifier())
                )
            ]
        );
    }

    public function joinAction(Request $request, string $gameId): JsonResponse
    {
        $this->connectFourCommandBus->handle(
            new JoinCommand(
                $gameId,
                $this->security->getUser()->getUserIdentifier()
            )
        );

        return new JsonResponse();
    }

    public function abortAction(Request $request, string $gameId): JsonResponse
    {
        $this->connectFourCommandBus->handle(
            new AbortCommand(
                $gameId,
                $this->security->getUser()->getUserIdentifier()
            )
        );

        return new JsonResponse();
    }

    public function resignAction(Request $request, string $gameId): JsonResponse
    {
        $this->connectFourCommandBus->handle(
            new ResignCommand(
                $gameId,
                $this->security->getUser()->getUserIdentifier()
            )
        );

        return new JsonResponse();
    }

    public function moveAction(Request $request, string $gameId): JsonResponse
    {
        $this->connectFourCommandBus->handle(
            new MoveCommand(
                $gameId,
                $this->security->getUser()->getUserIdentifier(),
                (int)$request->request->get('column', -1)
            )
        );

        return new JsonResponse();
    }
}
