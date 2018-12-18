import { service } from '../../js/ConnectFour/GameService.js'

window.Gaming = window.Gaming || {};
window.Gaming.ConnectFour = window.Gaming.ConnectFour || {};

window.Gaming.ConnectFour.OpenGameButton = class
{
    /**
     * @param {Gaming.Common.EventPublisher} eventPublisher
     * @param {Node} button
     */
    constructor(eventPublisher, button)
    {
        this.eventPublisher = eventPublisher;
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
            service.abort(this.currentOpenGameId);
        }

        service.open().then((game) => {
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
            service.redirectTo(this.currentOpenGameId);
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
