var Gambling = Gambling || {};
Gambling.ConnectFour = Gambling.ConnectFour || {};

/**
 * @param {Gambling.Common.EventPublisher} eventPublisher
 * @param {Node} runningGames
 * @constructor
 */
Gambling.ConnectFour.RunningGames = class
{
    /**
     * @param {Gambling.Common.EventPublisher} eventPublisher
     * @param {Node} runningGames
     */
    constructor(eventPublisher, runningGames)
    {
        this.eventPublisher = eventPublisher;
        this.runningGames = runningGames;

        this.registerEventHandler();
    }

    onRunningGamesUpdated(event)
    {
        this.runningGames.innerText = event.payload.count;
    }

    registerEventHandler()
    {
        this.eventPublisher.subscribe({
            isSubscribedTo: (event) => {
                return event.name === 'ConnectFour.RunningGamesUpdated';
            },
            handle: this.onRunningGamesUpdated.bind(this)
        });
    }
};
