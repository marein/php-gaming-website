<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\Bot;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\Bot\Bot;
use Gaming\Identity\Domain\Model\Bot\Event\BotRegistered;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BotTest extends TestCase
{
    #[Test]
    public function itShouldRegister(): void
    {
        $botId = AccountId::generate();
        $bot = Bot::register($botId, 'marein');

        self::assertEquals(
            [new DomainEvent($botId->toString(), new BotRegistered($botId, 'marein'))],
            $bot->flushDomainEvents()
        );
    }
}
