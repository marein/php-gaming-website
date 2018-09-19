<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gambling\Common\ObjectMapper\Collection\ArrayMapper;
use Gambling\Common\ObjectMapper\DiscriminatorMapper;
use Gambling\Common\ObjectMapper\Mapper;
use Gambling\Common\ObjectMapper\ObjectMapper;
use Gambling\Common\ObjectMapper\Scalar\IntMapper;
use Gambling\ConnectFour\Domain\Game\WinningRule\CommonWinningRule;
use Gambling\ConnectFour\Domain\Game\WinningRule\DiagonalWinningRule;
use Gambling\ConnectFour\Domain\Game\WinningRule\HorizontalWinningRule;
use Gambling\ConnectFour\Domain\Game\WinningRule\MultipleWinningRule;
use Gambling\ConnectFour\Domain\Game\WinningRule\VerticalWinningRule;

final class WinningRuleMapper implements Mapper
{
    /**
     * @var DiscriminatorMapper $discriminatorMapper
     */
    private $discriminatorMapper;

    /**
     * WinningRuleMapper constructor.
     */
    public function __construct()
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

        $this->discriminatorMapper = $ruleDiscriminatorMapper;
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
