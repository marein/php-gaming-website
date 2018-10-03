var Gaming = Gaming || {};
Gaming.ConnectFour = Gaming.ConnectFour || {};

/**
 * @param {Gaming.Common.EventPublisher} eventPublisher
 * @param {Node} runningGames
 * @constructor
 */
Gaming.ConnectFour.RunningGames = class
{
    /**
     * @param {Gaming.Common.EventPublisher} eventPublisher
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
