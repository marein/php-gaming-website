<?php
declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Application;

use Gaming\Common\Application\InvokeApplicationLifeCycle;
use PHPUnit\Framework\TestCase;

final class InvokeApplicationLifeCycleTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldInvoke(): void
    {
        $applicationLifeCycle = new InvokeApplicationLifeCycle();

        $return = $applicationLifeCycle->run(
            static function () {
                return 12345;
            }
        );

        $this->assertSame(12345, $return);
    }
}
