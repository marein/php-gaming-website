<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\DomainException;
use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\Domain\Exception\Violations;

class GameException extends DomainException
{
    public static function notFound(): self
    {
        return new self(new Violations(new Violation('game_not_found')));
    }

    public static function alreadyRunning(): self
    {
        return new self(new Violations(new Violation('game_already_running')));
    }

    public static function notRunning(): self
    {
        return new self(new Violations(new Violation('game_not_running')));
    }

    public static function playerHasInvalidStone(): self
    {
        return new self(new Violations(new Violation('player_has_invalid_stone')));
    }

    public static function playersNotUnique(): self
    {
        return new self(new Violations(new Violation('players_not_unique')));
    }

    public static function playerNotOwner(): self
    {
        return new self(new Violations(new Violation('player_not_owner')));
    }

    public static function unexpectedPlayer(): self
    {
        return new self(new Violations(new Violation('unexpected_player')));
    }

    public static function outOfSize(): self
    {
        return new self(new Violations(new Violation('out_of_size')));
    }

    public static function columnAlreadyFilled(): self
    {
        return new self(new Violations(new Violation('column_already_filled')));
    }

    public static function noTimeout(): self
    {
        return new self(new Violations(new Violation('no_timeout')));
    }

    public static function invalidSizeTooSmall(int $width, int $height): self
    {
        return new self(
            new Violations(
                new Violation('invalid_size.too_small', [
                    new ViolationParameter('width', $width),
                    new ViolationParameter('height', $height)
                ])
            )
        );
    }

    public static function invalidSizeNotEven(int $width, int $height): self
    {
        return new self(
            new Violations(
                new Violation('invalid_size.not_even', [
                    new ViolationParameter('width', $width),
                    new ViolationParameter('height', $height)
                ])
            )
        );
    }

    public static function winningSequenceLengthTooShort(int $min, int $value): self
    {
        return new self(
            new Violations(
                new Violation('winning_sequence_length_too_short', [
                    new ViolationParameter('min', $min),
                    new ViolationParameter('value', $value)
                ])
            )
        );
    }
}
