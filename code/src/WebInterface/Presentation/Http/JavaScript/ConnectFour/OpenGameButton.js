var Gambling = Gambling || {};
Gambling.ConnectFour = Gambling.ConnectFour || {};

Gambling.ConnectFour.OpenGameButton = class
{
    /**
     * @param {Gambling.Common.EventPublisher} eventPublisher
     * @param {Gambling.ConnectFour.GameService} gameService
     * @param {Node} button
     */
    constructor(eventPublisher, gameService, button)
    {
        this.eventPublisher = eventPublisher;
        this.gameService = gameService;
        this.button = button;
        this.currentOpenGameId = '';

        this.registerEventHandler();
    }

    onButtonClick(event)
    {
        event.preventDefault();

        this.button.disabled = true;
        this.button.classList.add('loading-indicator');

        if (this.currentOpenGameId) {
            this.gameService.abort(this.currentOpenGameId);
        }

        this.gameService.open().then((game) => {
            this.currentOpenGameId = game.gameId;
            this.button.disabled = false;
            this.button.classList.remove('loading-indicator');
        }).catch(() => {
            this.button.disabled = false;
            this.button.classList.remove('loading-indicator');
        });
    }

    onPlayerJoined(event)
    {
        if (this.currentOpenGameId === event.payload.gameId) {
            this.gameService.redirectTo(this.currentOpenGameId);
        }
    }

    onGameAborted(event)
    {
        if (this.currentOpenGameId === event.payload.gameId) {
            this.currentOpenGameId = '';
        }
    }

    registerEventHandler()
    {
        this.button.addEventListener('click', this.onButtonClick.bind(this));

        this.eventPublisher.subscribe({
            isSubscribedTo: (event) => {
                return event.name === 'ConnectFour.PlayerJoined';
            },
            handle: this.onPlayerJoined.bind(this)
        });

        this.eventPublisher.subscribe({
            isSubscribedTo: (event) => {
                return event.name === 'ConnectFour.GameAborted';
            },
            handle: this.onGameAborted.bind(this)
        });
    }
};
