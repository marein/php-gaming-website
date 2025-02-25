<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Query\GameQuery;
use Gaming\ConnectFour\Application\Game\Query\GamesByPlayerQuery;
use Gaming\ConnectFour\Application\Game\Query\OpenGamesQuery;
use Gaming\ConnectFour\Application\Game\Query\RunningGamesQuery;
use Gaming\WebInterface\Infrastructure\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Twig\Environment;

final class PageController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly Bus $connectFourQueryBus
    ) {
    }

    public function lobbyAction(): Response
    {
        return new Response(
            $this->twig->render('@web-interface/lobby.html.twig', [
                'maximumNumberOfGamesInList' => 10,
                'openGames' => $this->connectFourQueryBus->handle(new OpenGamesQuery()),
                'runningGames' => $this->connectFourQueryBus->handle(new RunningGamesQuery())
            ])
        );
    }

    public function gameAction(string $id): Response
    {
        return new Response(
            $this->twig->render('@web-interface/game.html.twig', [
                'game' => $this->connectFourQueryBus->handle(new GameQuery($id))
            ])
        );
    }

    public function profileAction(#[CurrentUser] ?User $user, Request $request): Response
    {
        return new Response(
            $this->twig->render('@web-interface/profile.html.twig', [
                'games' => $user !== null
                    ? $this->connectFourQueryBus->handle(new GamesByPlayerQuery($user->getUserIdentifier()))->games()
                    : []
            ])
        );
    }
}
