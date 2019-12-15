<?php
declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\WebInterface\Application\ConnectFourService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class PageController
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ConnectFourService
     */
    private $connectFourService;

    /**
     * PageController constructor.
     *
     * @param Environment        $twig
     * @param ConnectFourService $connectFourService
     */
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
                'openGames'                  => $this->connectFourService->openGames()['games'],
                'runningGames'               => $this->connectFourService->runningGames()
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
                    $request->getSession()->get('user')
                )['games']
            ])
        );
    }
}
