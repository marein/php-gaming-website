<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

final class GameMapperFactory
{
    /**
     * @return GameMapper
     */
    public function create(): GameMapper
    {
        $sizeMapper = new SizeMapper();
        $stoneMapper = new StoneMapper();
        $winningRuleMapper = new WinningRuleMapper();
        $fieldMapper = new FieldMapper();
        $gameIdMapper = new GameIdMapper();
        $boardMapper = new BoardMapper(
            $sizeMapper,
            $fieldMapper
        );
        $playerMapper = new PlayerMapper(
            $stoneMapper
        );
        $playersMapper = new PlayersMapper(
            $playerMapper
        );
        $configurationMapper = new ConfigurationMapper(
            $sizeMapper,
            $winningRuleMapper
        );
        $stateMapper = new StateMapper(
            $winningRuleMapper,
            $boardMapper,
            $playerMapper,
            $playersMapper,
            $configurationMapper
        );

        return new GameMapper($gameIdMapper, $stateMapper);
    }
}
