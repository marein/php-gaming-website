var Gaming = Gaming || {};
Gaming.ConnectFour = Gaming.ConnectFour || {};

Gaming.ConnectFour.AbortGameButton = class
{
    /**
     * @param {Gaming.ConnectFour.GameService} gameService
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
        this.button.classList.add('loading-indicator');

        this.gameService.abort(gameId).then(() => {
            this.button.disabled = false;
            this.button.classList.remove('loading-indicator');
        }).catch(() => {
            // todo: Handle exception based on error.
            this.button.disabled = false;
            this.button.classList.remove('loading-indicator');
        });
    }

    registerEventHandler()
    {
        this.button.addEventListener('click', this.onButtonClick.bind(this));
    }
};
