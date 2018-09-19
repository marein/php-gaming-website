<?php
declare(strict_types=1);

namespace Gambling\Common\Application;

use PHPUnit\Framework\TestCase;

final class InvokeApplicationLifeCycleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldInvoke(): void
    {
        $applicationLifeCycle = new InvokeApplicationLifeCycle();

        $return = $applicationLifeCycle->run(function () {
            return 12345;
        });

        $this->assertSame(12345, $return);
    }
}
