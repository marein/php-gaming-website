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
        this.button.disabled = false;
            this.gameService.redirectTo(gameId);
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
