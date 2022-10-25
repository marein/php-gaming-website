<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ConnectFourService;
use Gaming\WebInterface\Infrastructure\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class PageController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ConnectFourService $connectFourService
    ) {
    }

    public function lobbyAction(User $user): Response
    {
        return new Response(
            $this->twig->render('@web-interface/lobby.html.twig', [
                'maximumNumberOfGamesInList' => 10,
                'openGames' => $this->connectFourService->openGames()['games'],
                'runningGames' => $this->connectFourService->runningGames(),
                'user' => $user
            ])
        );
    }

    public function gameAction(string $id): Response
    {
        return new Response(
            $this->twig->render('@web-interface/game.html.twig', [
                'game' => $this->connectFourService->game($id)
            ])
        );
    }

    public function profileAction(Request $request, User $user): Response
    {
        return new Response(
            $this->twig->render('@web-interface/profile.html.twig', [
                'games' => $this->connectFourService->gamesByPlayer(
                    $user->getUserIdentifier()
                )['games']
            ])
        );
    }
}
