var Gambling = Gambling || {};
Gambling.ConnectFour = Gambling.ConnectFour || {};

Gambling.ConnectFour.Game = class
{
    /**
     * @param {Gambling.Common.EventPublisher} eventPublisher
     * @param {Gambling.ConnectFour.GameService} gameService
     * @param {Node} gameHolder
     * @param {String} gameId
     * @param {{x:Number, y:Number, color:Number}[]} moves
     */
    constructor(eventPublisher, gameService, gameHolder, gameId, moves)
    {
        this.eventPublisher = eventPublisher;
        this.gameService = gameService;
        this.gameHolder = gameHolder;
        this.gameId = gameId;
        this.moves = moves;
        this.fields = this.gameHolder.querySelectorAll('.game__field');
        this.colorToClass = {
            1: 'game__field--red',
            2: 'game__field--yellow'
        };

        this.moves.forEach(this.addMove.bind(this));
        this.registerEventHandler();
    }

    /**
     * @param {{x:Number, y:Number, color:Number}} move
     */
    addMove(move)
    {
        let field = this.gameHolder.querySelector('.game__field[data-point="' + move.x + ' ' + move.y + '"]');
        field.classList.add(
            this.colorToClass[move.color]
        );
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
        this.addMove(event.payload);
    }

    registerEventHandler()
    {
        this.eventPublisher.subscribe({
            isSubscribedTo: (event) => {
                return event.name === 'ConnectFour.PlayerMoved';
            },
            handle: this.onPlayerMoved.bind(this)
        });

        this.fields.forEach((field) => {
            field.addEventListener('click', this.onFieldClick.bind(this));
        });
    }
};
