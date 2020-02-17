<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gaming\Common\ObjectMapper\Collection\ArrayMapper;
use Gaming\Common\ObjectMapper\DiscriminatorMapper;
use Gaming\Common\ObjectMapper\Mapper;
use Gaming\Common\ObjectMapper\ObjectMapper;
use Gaming\Common\ObjectMapper\Scalar\IntMapper;
use Gaming\ConnectFour\Domain\Game\WinningRule\CommonWinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\DiagonalWinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\HorizontalWinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\MultipleWinningRule;
use Gaming\ConnectFour\Domain\Game\WinningRule\VerticalWinningRule;

final class WinningRuleMapper implements Mapper
{
    /**
     * @var DiscriminatorMapper $discriminatorMapper
     */
    private DiscriminatorMapper $discriminatorMapper;

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
