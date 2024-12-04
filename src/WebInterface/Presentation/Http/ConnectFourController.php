<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Command\AbortCommand;
use Gaming\ConnectFour\Application\Game\Command\JoinCommand;
use Gaming\ConnectFour\Application\Game\Command\MoveCommand;
use Gaming\ConnectFour\Application\Game\Command\OpenCommand;
use Gaming\ConnectFour\Application\Game\Command\ResignCommand;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Gaming\WebInterface\Presentation\Http\Form\OpenType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConnectFourController extends AbstractController
{
    public function __construct(
        private readonly Bus $connectFourCommandBus,
        private readonly Security $security
    ) {
    }

    public function openAction(Request $request): Response
    {
        $form = $this->createForm(OpenType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            [$width, $height] = explode('x', $form->get('size')->getData());

            return $this->redirectToRoute('challenge', [
                'id' => $this->connectFourCommandBus->handle(
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

    public function joinAction(Request $request, string $gameId): JsonResponse
    {
        $this->connectFourCommandBus->handle(
            new JoinCommand(
                $gameId,
                $this->security->forceUser()->getUserIdentifier()
            )
        );

        return new JsonResponse();
    }

    public function abortAction(Request $request, string $gameId): JsonResponse
    {
        $this->connectFourCommandBus->handle(
            new AbortCommand(
                $gameId,
                $this->security->forceUser()->getUserIdentifier()
            )
        );

        return new JsonResponse();
    }

    public function abortChallengeAction(string $gameId): Response
    {
        try {
            $this->connectFourCommandBus->handle(
                new AbortCommand(
                    $gameId,
                    $this->security->forceUser()->getUserIdentifier()
                )
            );
        } finally {
            return $this->redirectToRoute('lobby');
        }
    }

    public function resignAction(Request $request, string $gameId): JsonResponse
    {
        $this->connectFourCommandBus->handle(
            new ResignCommand(
                $gameId,
                $this->security->forceUser()->getUserIdentifier()
            )
        );

        return new JsonResponse();
    }

    public function moveAction(Request $request, string $gameId): JsonResponse
    {
        $this->connectFourCommandBus->handle(
            new MoveCommand(
                $gameId,
                $this->security->forceUser()->getUserIdentifier(),
                (int)$request->request->get('column', -1)
            )
        );

        return new JsonResponse();
    }
}
