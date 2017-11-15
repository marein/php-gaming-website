var Gambling = Gambling || {};
Gambling.ConnectFour = Gambling.ConnectFour || {};

Gambling.ConnectFour.Game = class
{
    /**
     * @param {Gambling.Common.EventPublisher} eventPublisher
     * @param {Gambling.ConnectFour.GameService} gameService
     * @param {Node} game
     */
    constructor(eventPublisher, gameService, game)
    {
        this.eventPublisher = eventPublisher;
        this.gameService = gameService;
        this.game = game;
        this.fields = this.game.querySelectorAll('.game__field');
        this.colorToClass = {
            1: 'game__field--red',
            2: 'game__field--yellow'
        };

        this.registerEventHandler();
    }

    onFieldClick(event)
    {
        let cell = event.target;

        this.gameService.move(
            this.game.dataset.gameId,
            cell.dataset.column
        );
    }

    onPlayerMoved(event)
    {
        let payload = event.payload;
        if (this.game.dataset.gameId === payload.gameId) {
            let field = this.game.querySelector('.game__field[data-point="' + payload.x + ' ' + payload.y + '"]');
            let color = parseInt(payload.color);
            field.classList.add(
                this.colorToClass[color]
            );
        }
    }

    registerEventHandler()
    {
        this.eventPublisher.subscribe({
            isSubscribedTo: (event) => {
                return event.name === 'connect-four.player-moved';
            },
            handle: this.onPlayerMoved.bind(this)
        });

        this.fields.forEach((field) => {
            field.addEventListener('click', this.onFieldClick.bind(this));
        });
    }
};
