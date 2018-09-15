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

    public function iCanAbortAGameWithAJoinedFriend(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->click('[data-open-game-button]');
        $I->waitForElement('.game-list__game--user-game', 2);
        $gameId = $I->grabAttributeFrom('.game-list__game--user-game', 'data-game-id');

        $jane = $I->haveFriend('jane');
        $jane->does(
            function (AcceptanceTester $I) use ($gameId) {
                $I->amOnPage('/');
                $I->waitForElement('[data-game-id="' . $gameId . '"]', 2);
                $I->click('[data-game-id="' . $gameId . '"]');
                $I->waitForCurrentUrl('/game/' . $gameId, 2);
            }
        );

        $I->waitForCurrentUrl('/game/' . $gameId, 2);
        $I->click('#abort-game');

        // todo: Add missing assertion. Currently not possible because the user interface is not updated.
    }
}
