<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;

final class FragmentController extends AbstractController
{
    #[Cache(public: true, maxage: 10)]
    public function homeTileAction(): Response
    {
        return $this->render('@tic-tac-toe/home-tile.html.twig');
    }
}
