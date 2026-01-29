<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Usernames\Usernames;
use Gaming\TicTacToe\Application\Challenge\All\AllRequest;
use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallenge;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class LobbyController extends AbstractController
{
    public function __construct(
        private readonly Bus $queryBus,
        private readonly Usernames $usernames
    ) {
    }

    public function lobbyAction(): Response
    {
        return $this->render('@tic-tac-toe/lobby.html.twig', [
            'openChallenges' => $openChallenges = $this->queryBus->handle(new AllRequest(100))->openChallenges,
            'usernames' => $this->usernames->byIds(
                array_map(
                    static fn(OpenChallenge $openChallenge): string => $openChallenge->playerId,
                    $openChallenges
                )
            )
        ]);
    }
}
