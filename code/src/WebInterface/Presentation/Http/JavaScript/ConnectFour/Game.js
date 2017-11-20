var Gambling = Gambling || {};
Gambling.ConnectFour = Gambling.ConnectFour || {};

Gambling.ConnectFour.Game = class
{
    /**
     * @param {Gambling.Common.EventPublisher} eventPublisher
     * @param {Gambling.ConnectFour.GameService} gameService
     * @param {Node} gameHolder
     * @param {String} gameId
     */
    constructor(eventPublisher, gameService, gameHolder, gameId)
    {
        this.eventPublisher = eventPublisher;
        this.gameService = gameService;
        this.gameHolder = gameHolder;
        this.gameId = gameId;
        this.fields = this.gameHolder.querySelectorAll('.game__field');
        this.colorToClass = {
            1: 'game__field--red',
            2: 'game__field--yellow'
        };

        this.registerEventHandler();
    }

    onFieldClick(event)
    {
        let cell = event.target;

        this.gameHolder.classList.add('loading-indicator');

        this.gameService.move(
            this.gameId,
            cell.dataset.column
        ).then(() => {
            this.gameHolder.classList.remove('loading-indicator');
        }).catch(() => {
            // todo: Handle exception based on error
            this.gameHolder.classList.remove('loading-indicator');
        });
    }

    onPlayerMoved(event)
    {
        let payload = event.payload;
        if (this.gameId === payload.gameId) {
            let field = this.gameHolder.querySelector('.game__field[data-point="' + payload.x + ' ' + payload.y + '"]');
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
