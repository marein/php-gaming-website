<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Query\GameQuery;
use Gaming\ConnectFour\Application\Game\Query\GamesByPlayerQuery;
use Gaming\ConnectFour\Application\Game\Query\OpenGamesQuery;
use Gaming\ConnectFour\Application\Game\Query\RunningGamesQuery;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Gaming\WebInterface\Presentation\Http\Form\OpenType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PageController extends AbstractController
{
    public function __construct(
        private readonly Bus $connectFourQueryBus,
        private readonly Security $security
    ) {
    }

    public function lobbyAction(): Response
    {
        return $this->render('@web-interface/lobby.html.twig', [
            'maximumNumberOfGamesInList' => 10,
            'openGames' => $this->connectFourQueryBus->handle(new OpenGamesQuery()),
            'runningGames' => $this->connectFourQueryBus->handle(new RunningGamesQuery()),
            'user' => $this->security->getUser(),
            'openForm' => $this->createForm(OpenType::class)
        ]);
    }

    public function gameAction(string $id): Response
    {
        return $this->render('@web-interface/game.html.twig', [
            'game' => $this->connectFourQueryBus->handle(new GameQuery($id)),
            'user' => $this->security->getUser()
        ]);
    }

    public function challengeAction(string $id): Response
    {
        return $this->render('@web-interface/challenge.html.twig', [
            'game' => $this->connectFourQueryBus->handle(new GameQuery($id))
        ]);
    }

    public function profileAction(Request $request): Response
    {
        return $this->render('@web-interface/profile.html.twig', [
            'games' => $this->connectFourQueryBus->handle(
                new GamesByPlayerQuery($this->security->getUser()->getUserIdentifier())
            )->games()
        ]);
    }
}
