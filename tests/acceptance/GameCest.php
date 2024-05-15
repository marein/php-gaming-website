<?php

declare(strict_types=1);

use Codeception\Lib\Friend;
use Facebook\WebDriver\WebDriverKeys;

class GameCest
{
    public function iCanOpenAGameAndAbortIt(AcceptanceTester $I): void
    {
        $gameId = $this->prepareOpenGameScenario($I);

        $I->click('.table-success');
        $I->waitForElementNotVisible('[data-game-id="' . $gameId . '"]');
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

        $I->waitForElementNotVisible('#chat .gp-loading');

        $I->fillField('message', 'Hi Jane.');
        $I->pressKey('[name="message"]', WebDriverKeys::ENTER);

        $jane->does(
            static function (AcceptanceTester $I): void {
                $I->waitForText('Hi Jane.', 10, '#chat');
                $I->fillField('message', 'Hi.');
                $I->pressKey('[name="message"]', WebDriverKeys::ENTER);
            }
        );

        $I->waitForText('Hi.', 10, '#chat');
    }

    private function prepareOpenGameScenario(AcceptanceTester $I): string
    {
        $I->amOnPage('/');
        $I->click('[data-open-game-button]');
        $I->waitForElement('.table-success');

        return $I->grabAttributeFrom('.table-success', 'data-game-id');
    }

    private function prepareRunningGameScenario(AcceptanceTester $I, Friend $friend): string
    {
        $gameId = $this->prepareOpenGameScenario($I);

        $friend->does(
            static function (AcceptanceTester $I) use ($gameId): void {
                $I->amOnPage('/');
                $I->waitForElement('[data-game-id="' . $gameId . '"]');
                $I->click('[data-game-id="' . $gameId . '"]');
                $I->retry(10);
                $I->retrySeeCurrentUrlEquals('/game/' . $gameId);
            }
        );

        $I->retry(10);
        $I->retrySeeCurrentUrlEquals('/game/' . $gameId);

        return $gameId;
    }
}
