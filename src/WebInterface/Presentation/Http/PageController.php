<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Query\GameQuery;
use Gaming\ConnectFour\Application\Game\Query\GamesByPlayerQuery;
use Gaming\WebInterface\Infrastructure\Security\User;
use Gaming\WebInterface\Presentation\Http\Form\OpenType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class PageController extends AbstractController
{
    public function __construct(
        private readonly Bus $connectFourQueryBus
    ) {
    }

    public function lobbyAction(): Response
    {
        return $this->render('@web-interface/lobby.html.twig', [
            'openForm' => $this->createForm(OpenType::class)
        ]);
    }

    public function gameAction(string $id): Response
    {
        return $this->render('@web-interface/game.html.twig', [
            'game' => $this->connectFourQueryBus->handle(new GameQuery($id))
        ]);
    }

    public function challengeAction(string $id): Response
    {
        return $this->render('@web-interface/challenge.html.twig', [
            'game' => $this->connectFourQueryBus->handle(new GameQuery($id))
        ]);
    }

    public function profileAction(#[CurrentUser] ?User $user, Request $request): Response
    {
        return $this->render('@web-interface/profile.html.twig', [
            'games' => $user !== null
                ? $this->connectFourQueryBus->handle(new GamesByPlayerQuery($user->getUserIdentifier()))->games()
                : []
        ]);
    }
}
