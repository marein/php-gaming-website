var Gambling = Gambling || {};
Gambling.ConnectFour = Gambling.ConnectFour || {};

Gambling.ConnectFour.JoinGameButton = class
{
    /**
     * @param {Gambling.ConnectFour.GameService} gameService
     * @param {Node} button
     */
    constructor(gameService, button)
    {
        this.gameService = gameService;
        this.button = button;

        this.registerEventHandler();
    }

    onButtonClick(event)
    {
        event.preventDefault();

        let gameId = this.button.dataset.gameId;

        this.button.disabled = true;

        this.gameService.join(gameId).then(() => {
            this.gameService.redirectTo(gameId);
        });
    }

    registerEventHandler()
    {
        this.button.addEventListener('click', this.onButtonClick.bind(this));
    }
};
