<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGames;
use Gaming\ConnectFour\Application\Game\Query\RunningGamesQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MetricsController
{
    public function __construct(
        private readonly Bus $queryBus
    ) {
    }

    public function metricsAction(Request $request): Response
    {
        $runningGames = $this->queryBus->handle(new RunningGamesQuery());
        assert($runningGames instanceof RunningGames);

        $metrics = sprintf(
            '# HELP running_games Number of running games.' . PHP_EOL .
            '# TYPE running_games gauge' . PHP_EOL .
            'running_games{domain="connect-four"} %s ' . PHP_EOL,
            $runningGames->count()
        );

        return new Response(
            $metrics,
            200,
            ['content-type' => 'text/plain']
        );
    }
}
