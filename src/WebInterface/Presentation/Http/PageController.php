<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ConnectFourService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class PageController
{
    private Environment $twig;

    private ConnectFourService $connectFourService;

    public function __construct(
        Environment $twig,
        ConnectFourService $connectFourService
    ) {
        $this->twig = $twig;
        $this->connectFourService = $connectFourService;
    }

    public function lobbyAction(): Response
    {
        return new Response(
            $this->twig->render('@web-interface/lobby.html.twig', [
                'maximumNumberOfGamesInList' => 10,
                'openGames' => $this->connectFourService->openGames()['games'],
                'runningGames' => $this->connectFourService->runningGames()
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

    public function profileAction(Request $request): Response
    {
        return new Response(
            $this->twig->render('@web-interface/profile.html.twig', [
                'games' => $this->connectFourService->gamesByPlayer(
                    (string)$request->getSession()->get('user')
                )['games']
            ])
        );
    }
}
