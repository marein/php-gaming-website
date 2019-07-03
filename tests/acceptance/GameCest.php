<?php
declare(strict_types=1);

class GameCest
{
    public function iCanOpenAGameAndAbortIt(AcceptanceTester $I): void
    {
        $gameId = $this->prepareOpenGameScenario($I);

        $I->click('.game-list__game--user-game');
        $I->waitForElementNotVisible('[data-game-id="' . $gameId . '"]', 5);
    }

    public function iCanAbortAGameWithAJoinedFriend(AcceptanceTester $I): void
    {
        $jane = $I->haveFriend('jane');
        $this->prepareRunningGameScenario($I, $jane);

        $I->click('#abort-game');

        // todo: Add missing assertion. Currently not possible because the user interface is not updated.
    }

    public function iCanChatWithAFriend(AcceptanceTester $I): void
    {
        $jane = $I->haveFriend('jane');
        $this->prepareRunningGameScenario($I, $jane);

        $I->waitForElementNotVisible(
            ['xpath' => '//*[contains(@class, "loading-indicator") and @id="chat"]'],
            3
        );

        $I->fillField('message', 'Hi Jane.');
        $I->pressKey('[name="message"]', \Facebook\WebDriver\WebDriverKeys::ENTER);

        $jane->does(
            function (AcceptanceTester $I) {
                $I->waitForText('Hi Jane.', 3, '#chat');
                $I->fillField('message', 'Hi.');
                $I->pressKey('[name="message"]', \Facebook\WebDriver\WebDriverKeys::ENTER);
            }
        );

        $I->waitForText('Hi.', 3, '#chat');
    }

    private function prepareOpenGameScenario(AcceptanceTester $I): string
    {
        $I->amOnPage('/');
        $I->click('[data-open-game-button]');
        $I->waitForElement('.game-list__game--user-game', 2);

        return $I->grabAttributeFrom('.game-list__game--user-game', 'data-game-id');
    }

    private function prepareRunningGameScenario(AcceptanceTester $I, \Codeception\Lib\Friend $friend): string
    {
        $gameId = $this->prepareOpenGameScenario($I);

        $friend->does(
            function (AcceptanceTester $I) use ($gameId) {
                $I->amOnPage('/');
                $I->waitForElement('[data-game-id="' . $gameId . '"]', 2);
                $I->click('[data-game-id="' . $gameId . '"]');
                $I->waitForCurrentUrl('/game/' . $gameId, 2);
            }
        );

        $I->waitForCurrentUrl('/game/' . $gameId, 2);

        return $gameId;
    }
}
