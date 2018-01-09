<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gambling\Common\ObjectMapper\DiscriminatorMapper;
use Gambling\Common\ObjectMapper\Exception\MapperException;
use Gambling\Common\ObjectMapper\Mapper;
use Gambling\Common\ObjectMapper\ObjectMapper;
use Gambling\Common\ObjectMapper\Scalar\IntMapper;
use Gambling\ConnectFour\Domain\Game\State\Aborted;
use Gambling\ConnectFour\Domain\Game\State\Drawn;
use Gambling\ConnectFour\Domain\Game\State\Open;
use Gambling\ConnectFour\Domain\Game\State\Running;
use Gambling\ConnectFour\Domain\Game\State\Won;

final class StateMapper implements Mapper
{
    /**
     * @var DiscriminatorMapper $discriminatorMapper
     */
    private $discriminatorMapper;

    /**
     * StateMapper constructor.
     *
     * @param WinningRuleMapper   $winningRuleMapper
     * @param BoardMapper         $boardMapper
     * @param PlayerMapper        $playerMapper
     * @param PlayersMapper       $playersMapper
     * @param ConfigurationMapper $configurationMapper
     *
     * @throws MapperException
     */
    public function __construct(
        WinningRuleMapper $winningRuleMapper,
        BoardMapper $boardMapper,
        PlayerMapper $playerMapper,
        PlayersMapper $playersMapper,
        ConfigurationMapper $configurationMapper
    ) {
        $runningMapper = new ObjectMapper(Running::class);
        $runningMapper->addProperty('winningRule', $winningRuleMapper);
        $runningMapper->addProperty('numberOfMovesUntilDraw', new IntMapper());
        $runningMapper->addProperty('board', $boardMapper);
        $runningMapper->addProperty('players', $playersMapper);

        $openMapper = new ObjectMapper(Open::class);
        $openMapper->addProperty('player', $playerMapper);
        $openMapper->addProperty('configuration', $configurationMapper);

        $abortedMapper = new ObjectMapper(Aborted::class);

        $drawnMapper = new ObjectMapper(Drawn::class);

        $wonMapper = new ObjectMapper(Won::class);

        $stateDiscriminatorMapper = new DiscriminatorMapper('type');
        $stateDiscriminatorMapper->addDiscriminator(
            Running::class,
            $runningMapper,
            'running'
        );
        $stateDiscriminatorMapper->addDiscriminator(
            Open::class,
            $openMapper,
            'open'
        );
        $stateDiscriminatorMapper->addDiscriminator(
            Aborted::class,
            $abortedMapper,
            'aborted'
        );
        $stateDiscriminatorMapper->addDiscriminator(
            Drawn::class,
            $drawnMapper,
            'drawn'
        );
        $stateDiscriminatorMapper->addDiscriminator(
            Won::class,
            $wonMapper,
            'won'
        );

        $this->discriminatorMapper = $stateDiscriminatorMapper;
    }

    /**
     * @inheritdoc
     */
    public function serialize($value)
    {
        return $this->discriminatorMapper->serialize($value);
    }

    /**
     * @inheritdoc
     */
    public function deserialize($value)
    {
        return $this->discriminatorMapper->deserialize($value);
    }
}
