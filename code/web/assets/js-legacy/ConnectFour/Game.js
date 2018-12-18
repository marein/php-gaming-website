import { service } from '../../js/ConnectFour/GameService.js'

window.Gaming = window.Gaming || {};
window.Gaming.ConnectFour = window.Gaming.ConnectFour || {};

window.Gaming.ConnectFour.Game = class
{
    /**
     * @param {Gaming.Common.EventPublisher} eventPublisher
     * @param {Node} gameHolder
     * @param {Node} previousMoveButton
     * @param {Node} nextMoveButton
     * @param {Node} followMovesButton
     */
    constructor(
        eventPublisher,
        gameHolder,
        previousMoveButton,
        nextMoveButton,
        followMovesButton
    ) {
        this.eventPublisher = eventPublisher;
        this.gameHolder = gameHolder;
        this.previousMoveButton = previousMoveButton;
        this.nextMoveButton = nextMoveButton;
        this.followMovesButton = followMovesButton;
        this.game = Gaming.ConnectFour.Model.Game.fromObject(
            JSON.parse(this.gameHolder.dataset.game)
        );
        this.numberOfCurrentMoveInView = this.game.numberOfMoves();
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
            field.classList.remove('game__field--flash');
            field.classList.remove('game__field--red');
            field.classList.remove('game__field--yellow');
        });

        this.game.moves.slice(0, index).forEach(this.showMove.bind(this));
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

        this.fields.forEach((field) => {
            field.classList.remove('game__field--flash');
        });
        field.classList.add('game__field--flash');
    }

    /**
     * Updates the previous move, next move und follow moves button according to the state of
     * the number of the current move in view.
     */
    updateNavigationButtons()
    {
        let isCurrentMoveTheLastMove = this.numberOfCurrentMoveInView === this.game.numberOfMoves();
        let isCurrentMoveBeforeTheFirstMove = this.numberOfCurrentMoveInView === 0;

        this.nextMoveButton.disabled = isCurrentMoveTheLastMove;
        this.followMovesButton.disabled = isCurrentMoveTheLastMove;
        this.previousMoveButton.disabled = isCurrentMoveBeforeTheFirstMove;

        this.nextMoveButton.classList.toggle('disabled', isCurrentMoveTheLastMove);
        this.followMovesButton.classList.toggle('disabled', isCurrentMoveTheLastMove);
        this.previousMoveButton.classList.toggle('disabled', isCurrentMoveBeforeTheFirstMove);

        // Remove flashing if the user follow the moves.
        if (isCurrentMoveTheLastMove) {
            this.followMovesButton.classList.remove('button--yellow');
            this.followMovesButton.classList.remove('button--flash');
        }
    }

    /**
     * Display the move only if the user looks at the last move.
     *
     * @param {{x:Number, y:Number, color:Number}} move
     */
    onMoveAppendedToGame(move)
    {
        // Only show if the the user follow the moves. Otherwise notify user that a new move is available.
        if (this.followMovesButton.disabled === true) {
            this.showMove(move);
            this.numberOfCurrentMoveInView++;
            this.updateNavigationButtons();
        } else {
            this.followMovesButton.classList.add('button--yellow');
            this.followMovesButton.classList.add('button--flash');
        }
    }

    onFieldClick(event)
    {
        let cell = event.target;

        this.gameHolder.classList.add('loading-indicator');

        service.move(
            this.game.gameId,
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
        this.game.appendMove({
            x: event.payload.x,
            y: event.payload.y,
            color: event.payload.color,
        });
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

        this.numberOfCurrentMoveInView = this.game.numberOfMoves();
        this.showMovesUpTo(this.numberOfCurrentMoveInView);
    }

    registerEventHandler()
    {
        this.game.onMoveAppended(this.onMoveAppendedToGame.bind(this));

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
