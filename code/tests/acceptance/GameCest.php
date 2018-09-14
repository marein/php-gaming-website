<?php

class GameCest
{
    public function iCanOpenAGameAndAbortIt(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->click('[data-open-game-button]');
        $I->waitForElement('.game-list__game', 2);
        $I->click('.game-list__game');
        $I->waitForElementNotVisible('.game-list__game', 5);
    }
}
