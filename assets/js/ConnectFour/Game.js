import { service } from './GameService.js'
import { Game as GameModel } from './Model/Game.js'

/**
 * todo: Render the game within <canvas>, see https://github.com/marein/php-gaming-website/issues/19.
 */

class GameElement extends HTMLElement
{
    connectedCallback()
    {
        this._onDisconnect = [];

        let game = JSON.parse(this.getAttribute('game'));

        this._gameHolder = document.createElement('div');
        this._gameHolder.classList.add('box');

        let table = document.createElement('table');
        table.classList.add('game');

        let tbody = document.createElement('tbody');

        for (let y = 1; y <= game.height; y++) {
            let tr = document.createElement('tr');

            for (let x = 1; x <= game.width; x++) {
                let td = document.createElement('td');
                td.classList.add('game__field');
                td.setAttribute('data-column', x.toString());
                td.setAttribute('data-point', x.toString() + ' ' + y.toString());

                tr.append(td);
            }

            tbody.append(tr);
        }

        table.append(tbody);
        this._gameHolder.append(table);
        this.append(this._gameHolder);

        this._previousMoveButton = document.querySelector(
            this.getAttribute('previous-move-selector')
        );
        this._nextMoveButton = document.querySelector(
            this.getAttribute('next-move-selector')
        );
        this._followMovesButton = document.querySelector(
            this.getAttribute('follow-moves-selector')
        );
        this._game = GameModel.fromObject(game);
        this._numberOfCurrentMoveInView = this._game.numberOfMoves();
        this._fields = this._gameHolder.querySelectorAll('.game__field');
        this._colorToClass = {
            1: 'game__field--red',
            2: 'game__field--yellow'
        };

        this._showMovesUpTo(this._numberOfCurrentMoveInView);

        this._registerEventHandler();
    }

    disconnectedCallback()
    {
        this._onDisconnect.forEach(f => f());
    }

    /**
     * @param {Number} index
     */
    _showMovesUpTo(index)
    {
        // Clear fields
        this._fields.forEach((field) => {
            field.classList.remove('game__field--flash');
            field.classList.remove('game__field--red');
            field.classList.remove('game__field--yellow');
        });

        this._game.moves.slice(0, index).forEach(this._showMove.bind(this));
        this._updateNavigationButtons();
    }

    /**
     * Display the move in the view.
     *
     * @param {{x:Number, y:Number, color:Number}} move
     */
    _showMove(move)
    {
        let field = this._gameHolder.querySelector('.game__field[data-point="' + move.x + ' ' + move.y + '"]');
        field.classList.add(
            this._colorToClass[move.color]
        );

        this._fields.forEach((field) => {
            field.classList.remove('game__field--flash');
        });
        field.classList.add('game__field--flash');
    }

    /**
     * Updates the previous move, next move und follow moves button according to the state of
     * the number of the current move in view.
     */
    _updateNavigationButtons()
    {
        let isCurrentMoveTheLastMove = this._numberOfCurrentMoveInView === this._game.numberOfMoves();
        let isCurrentMoveBeforeTheFirstMove = this._numberOfCurrentMoveInView === 0;

        this._nextMoveButton.disabled = isCurrentMoveTheLastMove;
        this._followMovesButton.disabled = isCurrentMoveTheLastMove;
        this._previousMoveButton.disabled = isCurrentMoveBeforeTheFirstMove;

        this._nextMoveButton.classList.toggle('disabled', isCurrentMoveTheLastMove);
        this._followMovesButton.classList.toggle('disabled', isCurrentMoveTheLastMove);
        this._previousMoveButton.classList.toggle('disabled', isCurrentMoveBeforeTheFirstMove);

        // Remove flashing if the user follow the moves.
        if (isCurrentMoveTheLastMove) {
            this._followMovesButton.classList.remove('button--yellow');
            this._followMovesButton.classList.remove('button--flash');
        }
    }

    /**
     * Display the move only if the user looks at the last move.
     *
     * @param {{x:Number, y:Number, color:Number}} move
     */
    _onMoveAppendedToGame(move)
    {
        // Only show if the the user follow the moves. Otherwise notify user that a new move is available.
        if (this._followMovesButton.disabled === true) {
            this._showMove(move);
            this._numberOfCurrentMoveInView++;
            this._updateNavigationButtons();
        } else {
            this._followMovesButton.classList.add('button--yellow');
            this._followMovesButton.classList.add('button--flash');
        }
    }

    _onFieldClick(event)
    {
        let cell = event.target;

        this._gameHolder.classList.add('loading-indicator');

        service.move(
            this._game.gameId,
            cell.dataset.column
        ).then(() => {
            this._gameHolder.classList.remove('loading-indicator');
        }).catch(() => {
            // todo: Handle exception based on error
            this._gameHolder.classList.remove('loading-indicator');
        });
    }

    _onPlayerMoved(event)
    {
        this._game.appendMove({
            x: event.detail.x,
            y: event.detail.y,
            color: event.detail.color,
        });
    }

    _onPreviousMoveClick(event)
    {
        event.preventDefault();

        this._numberOfCurrentMoveInView--;
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onNextMoveClick(event)
    {
        event.preventDefault();

        this._numberOfCurrentMoveInView++;
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onFollowMovesClick(event)
    {
        event.preventDefault();

        this._numberOfCurrentMoveInView = this._game.numberOfMoves();
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _registerEventHandler()
    {
        this._game.onMoveAppended(this._onMoveAppendedToGame.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.PlayerMoved', this._onPlayerMoved.bind(this));

        this._fields.forEach((field) => {
            field.addEventListener('click', this._onFieldClick.bind(this));
        });

        this._previousMoveButton.addEventListener('click', this._onPreviousMoveClick.bind(this));
        this._nextMoveButton.addEventListener('click', this._onNextMoveClick.bind(this));
        this._followMovesButton.addEventListener('click', this._onFollowMovesClick.bind(this));
    }
}

customElements.define('connect-four-game', GameElement);
