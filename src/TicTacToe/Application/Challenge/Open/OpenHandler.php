<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\Open;

use Gaming\Common\Timer\MoveTimer;
use Gaming\Common\Timer\TimerFactory;
use Gaming\TicTacToe\Domain\Challenge\Challenge;
use Gaming\TicTacToe\Domain\Challenge\Challenges;
use Gaming\TicTacToe\Domain\Game\Configuration;
use Gaming\TicTacToe\Domain\Game\Token;

final class OpenHandler
{
    public function __construct(
        public readonly Challenges $challenges
    ) {
    }

    public function __invoke(OpenRequest $request): OpenResponse
    {
        $challengeId = $this->challenges->nextIdentity();

        $this->challenges->add(
            Challenge::open(
                $challengeId,
                $request->playerId,
                new Configuration(
                    $request->size,
                    Token::tryFrom($request->token),
                    TimerFactory::fromString($request->timer) ?? MoveTimer::set(15000)
                )
            )
        );

        return new OpenResponse($challengeId->toString());
    }
}
