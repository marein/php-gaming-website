<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

final class GameMapperFactory
{
    /**
     * @return GameMapper
     */
    public function create(): GameMapper
    {
        // If the game mapper is not created, create it.
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
        $configurationMapper = new ConfigurationMapper(
            $sizeMapper,
            $winningRuleMapper
        );
        $stateMapper = new StateMapper(
            $winningRuleMapper,
            $boardMapper,
            $playerMapper,
            $configurationMapper
        );

        return new GameMapper($gameIdMapper, $stateMapper);
    }
}
