<?php

class GameCest
{
    public function iCanOpenAGameAndAbortIt(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->click('[data-open-game-button]');
        $I->waitForElement('.game-list__game--user-game', 2);
        $gameId = $I->grabAttributeFrom('.game-list__game--user-game', 'data-game-id');
        $I->click('.game-list__game--user-game');
        $I->waitForElementNotVisible('[data-game-id="' . $gameId . '"]', 5);
    }
}
