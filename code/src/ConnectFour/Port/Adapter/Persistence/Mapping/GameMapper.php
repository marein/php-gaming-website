<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gambling\Common\ObjectMapper\Collection\ArrayMapper;
use Gambling\Common\ObjectMapper\DiscriminatorMapper;
use Gambling\Common\ObjectMapper\Mapper;
use Gambling\Common\ObjectMapper\ObjectMapper;
use Gambling\Common\ObjectMapper\Scalar\IntMapper;
use Gambling\Common\ObjectMapper\Scalar\StringMapper;
use Gambling\ConnectFour\Domain\Game\Board;
use Gambling\ConnectFour\Domain\Game\Configuration;
use Gambling\ConnectFour\Domain\Game\Field;
use Gambling\ConnectFour\Domain\Game\Game;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Player;
use Gambling\ConnectFour\Domain\Game\Point;
use Gambling\ConnectFour\Domain\Game\Size;
use Gambling\ConnectFour\Domain\Game\State\Aborted;
use Gambling\ConnectFour\Domain\Game\State\Drawn;
use Gambling\ConnectFour\Domain\Game\State\Open;
use Gambling\ConnectFour\Domain\Game\State\Running;
use Gambling\ConnectFour\Domain\Game\State\Won;
use Gambling\ConnectFour\Domain\Game\Stone;
use Gambling\ConnectFour\Domain\Game\WinningRule\CommonWinningRule;
use Gambling\ConnectFour\Domain\Game\WinningRule\DiagonalWinningRule;
use Gambling\ConnectFour\Domain\Game\WinningRule\HorizontalWinningRule;
use Gambling\ConnectFour\Domain\Game\WinningRule\MultipleWinningRule;
use Gambling\ConnectFour\Domain\Game\WinningRule\VerticalWinningRule;

final class GameMapper
{
    /**
     * @var ObjectMapper $gameMapper
     */
    private $gameMapper;

    /**
     * Serialize the game to array.
     *
     * @param Game $game
     *
     * @return array
     */
    public function serialize(Game $game): array
    {
        return $this->gameMapper()->serialize($game);
    }

    /**
     * Deserialize the array to game.
     *
     * @param array $serializedGame
     *
     * @return Game
     */
    public function deserialize(array $serializedGame): Game
    {
        /** @var Game $game */
        $game = $this->gameMapper()->deserialize($serializedGame);

        return $game;
    }

    /**
     * Lazy load the mapper.
     *
     * @return ObjectMapper
     */
    private function gameMapper()
    {
        // If the game mapper is not created, create it.
        if (!$this->gameMapper) {
            $sizeMapper = $this->sizeMapper();
            $stoneMapper = $this->stoneMapper();
            $winningRuleMapper = $this->winningRuleMapper();
            $fieldMapper = $this->fieldMapper();
            $gameIdMapper = $this->gameIdMapper();
            $boardMapper = $this->boardMapper($sizeMapper, $fieldMapper);
            $playerMapper = $this->playerMapper($stoneMapper);
            $configurationMapper = $this->configurationMapper($sizeMapper, $winningRuleMapper);
            $stateMapper = $this->stateMapper(
                $winningRuleMapper,
                $boardMapper,
                $playerMapper,
                $configurationMapper
            );

            $gameMapper = new ObjectMapper(Game::class);
            $gameMapper->addProperty('gameId', $gameIdMapper);
            $gameMapper->addProperty('state', $stateMapper);
            $gameMapper->addProperty('chatId', new StringMapper());
            $gameMapper->addProperty('domainEvents', new class implements Mapper
            {
                public function serialize($value)
                {
                    return null;
                }

                public function deserialize($value)
                {
                    return new \ArrayObject();
                }
            });

            $this->gameMapper = $gameMapper;
        }

        return $this->gameMapper;
    }

    /**
     * Create a player mapper.
     *
     * @param Mapper $stoneMapper
     *
     * @return Mapper
     */
    private function playerMapper(Mapper $stoneMapper): Mapper
    {
        $playerMapper = new ObjectMapper(Player::class);
        $playerMapper->addProperty('playerId', new StringMapper());
        $playerMapper->addProperty('stone', $stoneMapper);

        return $playerMapper;
    }

    /**
     * Create a board mapper.
     *
     * @param Mapper $sizeMapper
     * @param Mapper $fieldMapper
     *
     * @return Mapper
     */
    private function boardMapper(Mapper $sizeMapper, Mapper $fieldMapper): Mapper
    {
        $boardMapper = new ObjectMapper(Board::class);
        $boardMapper->addProperty('size', $sizeMapper);
        $boardMapper->addProperty('fields', new ArrayMapper($fieldMapper));
        $boardMapper->addProperty('lastUsedField', $fieldMapper);

        return $boardMapper;
    }

    /**
     * Create a configuration mapper.
     *
     * @param Mapper $sizeMapper
     * @param Mapper $winningRuleMapper
     *
     * @return Mapper
     */
    private function configurationMapper(Mapper $sizeMapper, Mapper $winningRuleMapper): Mapper
    {
        $configurationMapper = new ObjectMapper(Configuration::class);
        $configurationMapper->addProperty('size', $sizeMapper);
        $configurationMapper->addProperty('winningRule', $winningRuleMapper);

        return $configurationMapper;
    }

    /**
     * Create a field mapper.
     *
     * @return Mapper
     */
    private function fieldMapper(): Mapper
    {
        // Return a specific mapper to shrink the output.
        // The field gets serialized to x|y|color.
        return new class implements Mapper
        {
            public function serialize($field)
            {
                // The field can be null in Board::lastUsedField
                if ($field === null) {
                    return null;
                }

                /** @var Field $field */
                return sprintf(
                    '%s|%s|%s',
                    $field->point()->x(),
                    $field->point()->y(),
                    (string)$field
                );
            }

            public function deserialize($serializedField)
            {
                // The field can be null in Board::lastUsedField
                if ($serializedField === null) {
                    return null;
                }

                [$x, $y, $color] = explode('|', $serializedField);
                $x = (int)$x;
                $y = (int)$y;
                $color = (int)$color;

                $point = new Point($x, $y);
                $field = Field::empty($point);

                if ($color == Stone::RED) {
                    $field = $field->placeStone(Stone::red());
                } elseif ($color == Stone::YELLOW) {
                    $field = $field->placeStone(Stone::yellow());
                }

                return $field;
            }
        };
    }

    /**
     * Create a stone mapper.
     *
     * @return Mapper
     */
    private function stoneMapper(): Mapper
    {
        $stoneMapper = new ObjectMapper(Stone::class);
        $stoneMapper->addProperty('color', new IntMapper());

        return $stoneMapper;
    }

    /**
     * Create a size mapper.
     *
     * @return Mapper
     */
    private function sizeMapper(): Mapper
    {
        $sizeMapper = new ObjectMapper(Size::class);
        $sizeMapper->addProperty('width', new IntMapper());
        $sizeMapper->addProperty('height', new IntMapper());

        return $sizeMapper;
    }

    /**
     * Create a game id mapper.
     *
     * @return Mapper
     */
    private function gameIdMapper(): Mapper
    {
        // Return a specific mapper for uuid serialization.
        return new class implements Mapper
        {
            public function serialize($gameId)
            {
                return $gameId->toString();
            }

            public function deserialize($serializedGameId)
            {
                return GameId::fromString($serializedGameId);
            }
        };
    }

    /**
     * Create a winning rule mapper.
     *
     * @return Mapper
     */
    private function winningRuleMapper(): Mapper
    {
        $ruleDiscriminatorMapper = new DiscriminatorMapper('type');

        $horizontalWinningRuleMapper = new ObjectMapper(HorizontalWinningRule::class);
        $horizontalWinningRuleMapper->addProperty(
            'numberOfRequiredMatches',
            new IntMapper()
        );

        $verticalWinningRuleMapper = new ObjectMapper(VerticalWinningRule::class);
        $verticalWinningRuleMapper->addProperty(
            'numberOfRequiredMatches',
            new IntMapper()
        );

        $diagonalWinningRuleMapper = new ObjectMapper(DiagonalWinningRule::class);
        $diagonalWinningRuleMapper->addProperty(
            'numberOfRequiredMatches',
            new IntMapper()
        );

        $multipleWinningRuleMapper = new ObjectMapper(MultipleWinningRule::class);
        $multipleWinningRuleMapper->addProperty(
            'winningRules',
            new ArrayMapper($ruleDiscriminatorMapper)
        );

        $commonWinningRuleMapper = new ObjectMapper(CommonWinningRule::class);
        $commonWinningRuleMapper->addProperty('winningRule', $ruleDiscriminatorMapper);

        $ruleDiscriminatorMapper->addDiscriminator(
            HorizontalWinningRule::class,
            $horizontalWinningRuleMapper,
            'horizontal'
        );
        $ruleDiscriminatorMapper->addDiscriminator(
            VerticalWinningRule::class,
            $verticalWinningRuleMapper,
            'vertical'
        );
        $ruleDiscriminatorMapper->addDiscriminator(
            DiagonalWinningRule::class,
            $diagonalWinningRuleMapper,
            'diagonal'
        );
        $ruleDiscriminatorMapper->addDiscriminator(
            CommonWinningRule::class,
            $commonWinningRuleMapper,
            'common'
        );
        $ruleDiscriminatorMapper->addDiscriminator(
            MultipleWinningRule::class,
            $multipleWinningRuleMapper,
            'multiple'
        );

        return $ruleDiscriminatorMapper;
    }

    /**
     * Create a state mapper.
     *
     * @param Mapper $winningRuleMapper
     * @param Mapper $boardMapper
     * @param Mapper $playerMapper
     * @param Mapper $configurationMapper
     *
     * @return Mapper
     */
    private function stateMapper(
        Mapper $winningRuleMapper,
        Mapper $boardMapper,
        Mapper $playerMapper,
        Mapper $configurationMapper
    ): Mapper {
        $runningMapper = new ObjectMapper(Running::class);
        $runningMapper->addProperty('winningRule', $winningRuleMapper);
        $runningMapper->addProperty('numberOfMovesUntilDraw', new IntMapper());
        $runningMapper->addProperty('board', $boardMapper);
        $runningMapper->addProperty('players', new ArrayMapper($playerMapper));

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

        return $stateDiscriminatorMapper;
    }
}
