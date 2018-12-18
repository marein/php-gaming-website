import { service } from '../../js/ConnectFour/GameService.js'

window.Gaming = window.Gaming || {};
window.Gaming.ConnectFour = window.Gaming.ConnectFour || {};

window.Gaming.ConnectFour.JoinGameButton = class
{
    /**
     * @param {Node} button
     */
    constructor(button)
    {
        this.button = button;

        this.registerEventHandler();
    }

    onButtonClick(event)
    {
        event.preventDefault();

        let gameId = this.button.dataset.gameId;

        this.button.disabled = true;

        service.join(gameId).then(() => {
            this.button.disabled = false;
            service.redirectTo(gameId);
        }).catch(() => {
            // todo: Handle exception based on error
            this.button.disabled = false;
        });
    }

    registerEventHandler()
    {
        this.button.addEventListener('click', this.onButtonClick.bind(this));
    }
};
