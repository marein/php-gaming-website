<?php
declare(strict_types=1);

namespace Gambling\Memory\Domain\Model\Game;

use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        $id = uniqid();

        $player = new Player($id);

        $this->assertEquals($id, $player->id());
    }
}
