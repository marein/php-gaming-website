<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Command\AbortCommand;
use Gaming\ConnectFour\Application\Game\Command\JoinCommand;
use Gaming\ConnectFour\Application\Game\Command\OpenCommand;
use Gaming\ConnectFour\Application\Game\Query\GameQuery;
use Gaming\ConnectFour\Port\Adapter\Http\Form\OpenType;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ChallengeController extends AbstractController
{
    public function __construct(
        private readonly Bus $commandBus,
        private readonly Bus $queryBus,
        private readonly Security $security
    ) {
    }

    public function openAction(Request $request): Response
    {
        $form = $this->createForm(OpenType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            [$width, $height] = explode('x', $form->get('size')->getData());

            return $this->redirectToRoute('connect_four_challenge', [
                'id' => $this->commandBus->handle(
                    new OpenCommand(
                        $this->security->forceUser()->getUserIdentifier(),
                        (int)$width,
                        (int)$height,
                        (int)$form->get('color')->getData()
                    )
                )
            ]);
        }

        return $this->redirectToRoute('lobby');
    }

    public function abortChallengeAction(string $id): Response
    {
        $this->commandBus->handle(
            new AbortCommand(
                $id,
                $this->security->forceUser()->getUserIdentifier()
            )
        );

        return $this->redirectToRoute('lobby');
    }

    public function acceptChallengeAction(string $id): Response
    {
        $this->commandBus->handle(
            new JoinCommand(
                $id,
                $this->security->forceUser()->getUserIdentifier()
            )
        );

        return $this->redirectToRoute('game', ['id' => $id]);
    }

    public function showAction(string $id): Response
    {
        $game = $this->queryBus->handle(new GameQuery($id));

        if ($game->state !== $game::STATE_OPEN) {
            return $this->redirectToRoute('game', ['id' => $id], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->render('@connect-four/challenge.html.twig', [
            'game' => $game
        ]);
    }
}
