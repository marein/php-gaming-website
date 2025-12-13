<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Memory\Domain\Model\Game;

use Gaming\Memory\Domain\Model\Game\Player;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    #[Test]
    public function itShouldBeCreatedWithItsValues(): void
    {
        $id = uniqid();

        $player = new Player($id);

        $this->assertEquals($id, $player->id());
    }
}
