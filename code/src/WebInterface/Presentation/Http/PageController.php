<?php
declare(strict_types=1);

namespace Gambling\WebInterface\Presentation\Http;

use Gambling\WebInterface\Application\ConnectFourService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

final class PageController
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var ConnectFourService
     */
    private $connectFourService;

    /**
     * PageController constructor.
     *
     * @param EngineInterface    $templating
     * @param ConnectFourService $connectFourService
     */
    public function __construct(
        EngineInterface $templating,
        ConnectFourService $connectFourService
    ) {
        $this->templating = $templating;
        $this->connectFourService = $connectFourService;
    }

    public function lobbyAction(): Response
    {
        return new Response(
            $this->templating->render('@web-interface/lobby.html.twig', [
                'maximumNumberOfGamesInList' => 10,
                'openGames'                  => $this->connectFourService->openGames()['games'],
                'runningGames'               => $this->connectFourService->runningGames()
            ])
        );
    }

    public function gameAction(string $id): Response
    {
        return new Response(
            $this->templating->render('@web-interface/game.html.twig', [
                'game' => $this->connectFourService->game($id)
            ])
        );
    }

    public function profileAction(Request $request): Response
    {
        return new Response(
            $this->templating->render('@web-interface/profile.html.twig', [
                'games' => $this->connectFourService->gamesByPlayer(
                    $request->getSession()->get('user')
                )['games']
            ])
        );
    }
}
