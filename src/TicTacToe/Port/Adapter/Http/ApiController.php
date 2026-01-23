<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\TicTacToe\Application\Challenge\Accept\AcceptRequest;
use Gaming\TicTacToe\Application\Challenge\Withdraw\WithdrawRequest;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiController extends AbstractController
{
    public function __construct(
        private readonly Bus $commandBus,
        private readonly Security $security,
    ) {
    }

    public function acceptAction(string $id): Response
    {
        $this->commandBus->handle(
            new AcceptRequest(
                $id,
                $this->security->forceUser()->getUserIdentifier()
            )
        );

        return new JsonResponse();
    }

    public function withdrawAction(string $id): Response
    {
        $this->commandBus->handle(
            new WithdrawRequest(
                $id,
                $this->security->forceUser()->getUserIdentifier()
            )
        );

        return new JsonResponse();
    }
}
