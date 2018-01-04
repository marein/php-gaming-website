var Gambling = Gambling || {};
Gambling.ConnectFour = Gambling.ConnectFour || {};

Gambling.ConnectFour.Game = class
{
    /**
     * @param {Gambling.Common.EventPublisher} eventPublisher
     * @param {Gambling.ConnectFour.GameService} gameService
     * @param {Node} gameHolder
     * @param {Node} previousMoveButton
     * @param {Node} nextMoveButton
     * @param {Node} followMovesButton
     * @param {String} gameId
     * @param {{x:Number, y:Number, color:Number}[]} moves
     */
    constructor(
        eventPublisher,
        gameService,
        gameHolder,
        previousMoveButton,
        nextMoveButton,
        followMovesButton,
        gameId,
        moves
    )
    {
        this.eventPublisher = eventPublisher;
        this.gameService = gameService;
        this.gameHolder = gameHolder;
        this.previousMoveButton = previousMoveButton;
        this.nextMoveButton = nextMoveButton;
        this.followMovesButton = followMovesButton;
        this.gameId = gameId;
        this.moves = moves;
        this.numberOfCurrentMoveInView = this.moves.length;
        this.fields = this.gameHolder.querySelectorAll('.game__field');
        this.colorToClass = {
            1: 'game__field--red',
            2: 'game__field--yellow'
        };

        this.showMovesUpTo(this.numberOfCurrentMoveInView);
        this.registerEventHandler();
    }

    /**
     * @param {Number} index
     */
    showMovesUpTo(index)
    {
        // Clear fields
        this.fields.forEach((field) => {
            field.classList.remove('game__field--red');
            field.classList.remove('game__field--yellow');
        });

        this.moves.slice(0, index).forEach(this.showMove.bind(this));
        this.updateNavigationButtons();
    }

    /**
     * Display the move in the view.
     *
     * @param {{x:Number, y:Number, color:Number}} move
     */
    showMove(move)
    {
        let field = this.gameHolder.querySelector('.game__field[data-point="' + move.x + ' ' + move.y + '"]');
        field.classList.add(
            this.colorToClass[move.color]
        );
    }

    /**
     * Push the move in the list. Display the move only if the user looks at the last move.
     *
     * @param {{x:Number, y:Number, color:Number}} move
     */
    addMove(move)
    {
        this.moves.push(move);

        // Only show if the the user follow the moves.
        if (this.followMovesButton.disabled === true) {
            this.showMove(move);
            this.numberOfCurrentMoveInView++;
            this.updateNavigationButtons();
        }
    }

    /**
     * Updates the previous move, next move und follow moves button according to the state of
     * the number of the current move in view.
     */
    updateNavigationButtons()
    {
        let isCurrentMoveTheLastMove = this.numberOfCurrentMoveInView === this.moves.length;
        let isCurrentMoveBeforeTheFirstMove = this.numberOfCurrentMoveInView === 0;

        this.nextMoveButton.disabled = isCurrentMoveTheLastMove;
        this.followMovesButton.disabled = isCurrentMoveTheLastMove;
        this.previousMoveButton.disabled = isCurrentMoveBeforeTheFirstMove;

        this.nextMoveButton.classList.toggle('disabled', isCurrentMoveTheLastMove);
        this.followMovesButton.classList.toggle('disabled', isCurrentMoveTheLastMove);
        this.previousMoveButton.classList.toggle('disabled', isCurrentMoveBeforeTheFirstMove);
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

    onPreviousMoveClick(event)
    {
        event.preventDefault();

        this.numberOfCurrentMoveInView--;
        this.showMovesUpTo(this.numberOfCurrentMoveInView);
    }

    onNextMoveClick(event)
    {
        event.preventDefault();

        this.numberOfCurrentMoveInView++;
        this.showMovesUpTo(this.numberOfCurrentMoveInView);
    }

    onFollowMovesClick(event)
    {
        event.preventDefault();

        this.numberOfCurrentMoveInView = this.moves.length;
        this.showMovesUpTo(this.numberOfCurrentMoveInView);
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

        this.previousMoveButton.addEventListener('click', this.onPreviousMoveClick.bind(this));
        this.nextMoveButton.addEventListener('click', this.onNextMoveClick.bind(this));
        this.followMovesButton.addEventListener('click', this.onFollowMovesClick.bind(this));
    }
};
