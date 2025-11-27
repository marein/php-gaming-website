<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\Bot;

use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\Bot\Bot;
use Gaming\Identity\Domain\Model\Bot\Event\BotRegistered;
use PHPUnit\Framework\TestCase;

final class BotTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldRegister(): void
    {
        $botId = AccountId::generate();
        $bot = Bot::register($botId, 'marein');

        $domainEvents = $bot->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0]->content instanceof BotRegistered);
        self::assertEquals($botId->toString(), $domainEvents[0]->content->aggregateId());
        self::assertEquals('marein', $domainEvents[0]->content->username);
    }
}
