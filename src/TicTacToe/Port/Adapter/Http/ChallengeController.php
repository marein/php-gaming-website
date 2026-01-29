<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Usernames\Usernames;
use Gaming\TicTacToe\Application\Challenge\Accept\AcceptRequest;
use Gaming\TicTacToe\Application\Challenge\GetById\GetByIdRequest;
use Gaming\TicTacToe\Application\Challenge\Open\OpenRequest;
use Gaming\TicTacToe\Application\Challenge\Withdraw\WithdrawRequest;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ChallengeController extends AbstractController
{
    public function __construct(
        private readonly Bus $commandBus,
        private readonly Bus $queryBus,
        private readonly Usernames $usernames,
        private readonly Security $security,
    ) {
    }

    public function openAction(): Response
    {
        return $this->redirectToRoute('tic_tac_toe_challenge', [
            'id' => $this->commandBus->handle(
                new OpenRequest(
                    $this->security->forceUser()->getUserIdentifier(),
                    3,
                    0,
                    'move:15000'
                )
            )->challengeId
        ]);
    }

    public function showAction(string $id): Response
    {
        return $this->render('@tic-tac-toe/challenge/show.html.twig', [
            'challenge' => $challenge = $this->queryBus->handle(new GetByIdRequest($id))->challenge,
            'usernames' => $this->usernames->byIds([$challenge->challengerId])
        ]);
    }

    public function acceptAction(string $id): Response
    {
        $this->commandBus->handle(
            new AcceptRequest(
                $id,
                $this->security->forceUser()->getUserIdentifier()
            )
        );

        return $this->redirectToRoute('tic_tac_toe_lobby');
    }

    public function withdrawAction(string $id): Response
    {
        $this->commandBus->handle(
            new WithdrawRequest(
                $id,
                $this->security->forceUser()->getUserIdentifier()
            )
        );

        return $this->redirectToRoute('tic_tac_toe_lobby');
    }
}
