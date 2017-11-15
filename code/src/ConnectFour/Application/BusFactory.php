<?php

namespace Gambling\ConnectFour\Application;

use Doctrine\DBAL\Driver\Connection;
use Gambling\Common\Bus\Bus;
use Gambling\Common\Bus\CallableBus;
use Gambling\Common\Bus\DoctrineTransactionalBus;
use Gambling\ConnectFour\Application\Game\Command\AbortCommand;
use Gambling\ConnectFour\Application\Game\Command\AbortHandler;
use Gambling\ConnectFour\Application\Game\Command\AssignChatCommand;
use Gambling\ConnectFour\Application\Game\Command\AssignChatHandler;
use Gambling\ConnectFour\Application\Game\Command\JoinCommand;
use Gambling\ConnectFour\Application\Game\Command\JoinHandler;
use Gambling\ConnectFour\Application\Game\Command\MoveCommand;
use Gambling\ConnectFour\Application\Game\Command\MoveHandler;
use Gambling\ConnectFour\Application\Game\Command\OpenCommand;
use Gambling\ConnectFour\Application\Game\Command\OpenHandler;
use Gambling\ConnectFour\Application\Game\Query\GameHandler;
use Gambling\ConnectFour\Application\Game\Query\GameQuery;
use Gambling\ConnectFour\Application\Game\Query\GamesByPlayerHandler;
use Gambling\ConnectFour\Application\Game\Query\GamesByPlayerQuery;
use Gambling\ConnectFour\Application\Game\Query\OpenGamesHandler;
use Gambling\ConnectFour\Application\Game\Query\OpenGamesQuery;
use Gambling\ConnectFour\Application\Game\Query\RunningGamesHandler;
use Gambling\ConnectFour\Application\Game\Query\RunningGamesQuery;
use Gambling\ConnectFour\Domain\Game\Games;
use Gambling\ConnectFour\Port\Adapter\Persistence\Repository\PredisGameFinder;
use Gambling\ConnectFour\Port\Adapter\Persistence\Repository\PredisGamesByPlayerFinder;
use Gambling\ConnectFour\Port\Adapter\Persistence\Repository\PredisOpenGamesFinder;
use Gambling\ConnectFour\Port\Adapter\Persistence\Repository\PredisRunningGamesFinder;
use Predis\Client;

final class BusFactory
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Games
     */
    private $games;

    /**
     * @var Client
     */
    private $predis;

    /**
     * BusFactory constructor.
     *
     * @param Connection $connection
     * @param Games      $games
     * @param Client     $predis
     */
    public function __construct(Connection $connection, Games $games, Client $predis)
    {
        $this->connection = $connection;
        $this->games = $games;
        $this->predis = $predis;
    }

    /**
     * @return Bus
     */
    public function createCommandBus(): Bus
    {
        $bus = new CallableBus();

        $bus->addHandler(AbortCommand::class, function (AbortCommand $command) {
            return (new AbortHandler($this->games))($command);
        });
        $bus->addHandler(AssignChatCommand::class, function (AssignChatCommand $command) {
            return (new AssignChatHandler($this->games))($command);
        });
        $bus->addHandler(JoinCommand::class, function (JoinCommand $command) {
            return (new JoinHandler($this->games))($command);
        });
        $bus->addHandler(MoveCommand::class, function (MoveCommand $command) {
            return (new MoveHandler($this->games))($command);
        });
        $bus->addHandler(OpenCommand::class, function (OpenCommand $command) {
            return (new OpenHandler($this->games))($command);
        });

        $bus = new DoctrineTransactionalBus($bus, $this->connection);

        return $bus;
    }

    /**
     * @return Bus
     */
    public function createQueryBus(): Bus
    {
        $bus = new CallableBus();

        $bus->addHandler(GameQuery::class, function (GameQuery $query) {
            return (new GameHandler(
                new PredisGameFinder($this->predis),
                $this->games
            ))($query);
        });
        $bus->addHandler(GamesByPlayerQuery::class, function (GamesByPlayerQuery $query) {
            return (new GamesByPlayerHandler(
                new PredisGamesByPlayerFinder($this->predis)
            ))($query);
        });
        $bus->addHandler(OpenGamesQuery::class, function (OpenGamesQuery $query) {
            return (new OpenGamesHandler(
                new PredisOpenGamesFinder($this->predis)
            ))($query);
        });
        $bus->addHandler(RunningGamesQuery::class, function (RunningGamesQuery $query) {
            return (new RunningGamesHandler(
                new PredisRunningGamesFinder($this->predis)
            ))($query);
        });

        return $bus;
    }
}
