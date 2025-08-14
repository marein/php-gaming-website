<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Usernames\Usernames;
use Gaming\ConnectFour\Application\Game\Query\GameQuery;
use Gaming\ConnectFour\Application\Game\Query\GamesByPlayerQuery;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\State;
use Gaming\WebInterface\Infrastructure\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Twig\Environment;

final class PageController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Bus $connectFourQueryBus,
        private readonly Usernames $usernames
    ) {
    }

    public function lobbyAction(): Response
    {
        return new Response(
            $this->twig->render('@web-interface/lobby.html.twig')
        );
    }

    public function gameAction(string $id): Response
    {
        return new Response(
            $this->twig->render('@web-interface/game.html.twig', [
                'game' => $game = $this->connectFourQueryBus->handle(new GameQuery($id)),
                'usernames' => $game->state !== $game::STATE_OPEN
                    ? $this->usernames->byIds($game->players())
                    : []
            ])
        );
    }

    public function profileAction(#[CurrentUser] ?User $user, Request $request): Response
    {
        return new Response(
            $this->twig->render('@web-interface/profile.html.twig', [
                'gamesPerPage' => $gamesPerPage = 12,
                'gamesByPlayer' => $gamesByPlayer = $user === null
                    ? new GamesByPlayer(0, [])
                    : $this->connectFourQueryBus->handle(
                        new GamesByPlayerQuery(
                            $user->getUserIdentifier(),
                            State::tryFrom($request->query->getString('state', State::ALL->value)) ?? State::ALL,
                            $request->query->getInt('page', 1),
                            $gamesPerPage
                        )
                    ),
                    'usernames' => $this->usernames->byIds(
                        array_unique(
                            array_merge(
                                ...array_map(static fn(Game $game): array => $game->players(), $gamesByPlayer->games)
                            )
                        )
                    )
            ])
        );
    }
}
