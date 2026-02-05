<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Http;

use Gaming\TicTacToe\Port\Adapter\Http\Form\OpenType;
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

    #[Cache(public: true, maxage: 10)]
    public function openAction(): Response
    {
        return $this->render('@tic-tac-toe/open.html.twig', [
            'form' => $this->createForm(OpenType::class)
        ]);
    }
}
