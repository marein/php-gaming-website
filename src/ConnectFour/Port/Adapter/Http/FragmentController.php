<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Query\PlayerSearchStatistics\PlayerSearchStatisticsQuery;
use Gaming\ConnectFour\Application\Game\Query\OpenGamesQuery;
use Gaming\ConnectFour\Application\Game\Query\RunningGamesQuery;
use Gaming\ConnectFour\Port\Adapter\Http\Form\OpenType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class FragmentController extends AbstractController
{
    public function __construct(
        private readonly Bus $queryBus
    ) {
    }

    #[Cache(public: true, maxage: 10)]
    public function statisticsAction(): Response
    {
        return $this->render('@connect-four/statistics.html.twig', [
            'runningGames' => $this->queryBus->handle(new RunningGamesQuery())
        ]);
    }

    public function openGamesAction(): Response
    {
        return $this->render('@connect-four/open-games.html.twig', [
            'openGames' => $this->queryBus->handle(new OpenGamesQuery())
        ]);
    }

    #[Cache(public: true, maxage: 10)]
    public function openAction(): Response
    {
        return $this->render('@connect-four/open.html.twig', [
            'form' => $this->createForm(OpenType::class)
        ]);
    }

    public function playerSearchFilterAction(#[CurrentUser] ?UserInterface $user): Response
    {
        return $this->render('@connect-four/player-search-filter.html.twig', [
            'playerSearchStatistics' => $this->queryBus->handle(
                new PlayerSearchStatisticsQuery($user?->getUserIdentifier())
            )
        ]);
    }
}
