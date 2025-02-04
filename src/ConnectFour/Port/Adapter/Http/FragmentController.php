<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Query\RunningGamesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;

final class FragmentController extends AbstractController
{
    public function __construct(
        private readonly Bus $queryBus
    ) {
    }

    #[Cache(public: true, maxage: 10)]
    public function statisticsAction(): Response
    {
        return $this->render('@connect-four/running-games.html.twig', [
            'runningGames' => $this->queryBus->handle(new RunningGamesQuery())
        ]);
    }
}
