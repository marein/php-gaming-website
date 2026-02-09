<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\GetById;

use Gaming\Common\EventStore\EventStore;
use Gaming\TicTacToe\Application\Model\Challenge;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeOpened;
use Gaming\TicTacToe\Domain\Challenge\Exception\ChallengeException;

final class GetByIdHandler
{
    public function __construct(
        private readonly EventStore $eventStore
    ) {
    }

    public function __invoke(GetByIdRequest $request): GetByIdResponse
    {
        $domainEvents = $this->eventStore->byStreamId($request->challengeId);
        if (count($domainEvents) === 0 || !$domainEvents[0]->content instanceof ChallengeOpened) {
            throw ChallengeException::notFound();
        }

        return new GetByIdResponse(
            array_reduce(
                $domainEvents,
                static fn(Challenge $challenge, $domainEvent) => $challenge->apply($domainEvent->content),
                new Challenge($request->challengeId)
            )
        );
    }
}
