<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Messaging;

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
