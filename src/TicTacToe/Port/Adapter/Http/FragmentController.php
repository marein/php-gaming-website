<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Usernames\Usernames;
use Gaming\TicTacToe\Application\Challenge\All\AllRequest;
use Gaming\TicTacToe\Application\Model\OpenChallenges\OpenChallenge;
use Gaming\TicTacToe\Port\Adapter\Http\Form\OpenType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;

final class FragmentController extends AbstractController
{
    public function __construct(
        private readonly Bus $queryBus,
        private readonly Usernames $usernames
    ) {
    }

    #[Cache(public: true, maxage: 10)]
    public function statisticsAction(): Response
    {
        return $this->render('@tic-tac-toe/statistics.html.twig');
    }

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

    public function openChallengesAction(): Response
    {
        return $this->render('@tic-tac-toe/open-challenges.html.twig', [
            'openChallenges' => $openChallenges = $this->queryBus->handle(new AllRequest(100))->openChallenges,
            'usernames' => $this->usernames->byIds(
                array_map(
                    static fn(OpenChallenge $openChallenge): string => $openChallenge->challengerId,
                    $openChallenges
                )
            )
        ]);
    }
}
