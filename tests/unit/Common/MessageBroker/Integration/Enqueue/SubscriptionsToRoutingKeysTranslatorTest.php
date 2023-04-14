<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\MessageBroker\Integration\Enqueue;

use Gaming\Common\MessageBroker\Integration\Enqueue\SubscriptionsToRoutingKeysTranslator;
use Gaming\Common\MessageBroker\Model\Subscription\SpecificMessage;
use Gaming\Common\MessageBroker\Model\Subscription\WholeDomain;
use PHPUnit\Framework\TestCase;

class SubscriptionsToRoutingKeysTranslatorTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldTranslate(): void
    {
        $expectedRoutingKeys = [
            'MyDomain.MyName',
            'MyOtherDomain.#'
        ];

        $translator = new SubscriptionsToRoutingKeysTranslator(
            [
                new SpecificMessage('MyDomain', 'MyName'),
                new WholeDomain('MyOtherDomain')
            ]
        );

        $this->assertSame($expectedRoutingKeys, $translator->routingKeys());
    }
}
