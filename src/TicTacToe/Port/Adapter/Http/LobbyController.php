<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class LobbyController extends AbstractController
{
    public function lobbyAction(): Response
    {
        return $this->render('@tic-tac-toe/lobby.html.twig');
    }
}
