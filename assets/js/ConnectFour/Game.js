import {service} from './GameService.js'
import {Game as GameModel} from './Model/Game.js'

customElements.define('connect-four-game', class extends HTMLElement {
    connectedCallback() {
        this._onDisconnect = [];

        let game = JSON.parse(this.getAttribute('game'));

        this._gameNode = document.createElement('table');
        this._gameNode.classList.add('gp-game');

        let tbody = document.createElement('tbody');

        for (let y = 1; y <= game.height; y++) {
            let tr = document.createElement('tr');

            for (let x = 1; x <= game.width; x++) {
                let td = document.createElement('td');
                td.classList.add('gp-game__field');
                td.setAttribute('data-column', x.toString());
                td.setAttribute('data-point', x.toString() + ' ' + y.toString());

                tr.append(td);
            }

            tbody.append(tr);
        }

        this._gameNode.append(tbody);
        this.append(this._gameNode);

        this._previousMoveButton = document.querySelector(this.getAttribute('previous-move-selector'));
        this._nextMoveButton = document.querySelector(this.getAttribute('next-move-selector'));
        this._followMovesButton = document.querySelector(this.getAttribute('follow-moves-selector'));
        this._game = GameModel.fromObject(game);
        this._numberOfCurrentMoveInView = this._game.numberOfMoves();
        this._fields = this._gameNode.querySelectorAll('.gp-game__field');
        this._colorToClass = {
            1: 'gp-game__field--red',
            2: 'gp-game__field--yellow'
        };

        this._showMovesUpTo(this._numberOfCurrentMoveInView);

        this._registerEventHandler();
    }

    disconnectedCallback() {
        this._onDisconnect.forEach(f => f());
    }

    /**
     * @param {Number} index
     */
    _showMovesUpTo(index) {
        // Clear fields
        this._fields.forEach(field => {
            field.classList.remove('gp-heartbeat');
            field.classList.remove('gp-game__field--red');
            field.classList.remove('gp-game__field--yellow');
        });

        this._game.moves.slice(0, index).forEach(this._showMove.bind(this));
        this._updateNavigationButtons();
    }

    /**
     * Display the move in the view.
     *
     * @param {{x:Number, y:Number, color:Number}} move
     */
    _showMove(move) {
        let field = this._gameNode.querySelector(`.gp-game__field[data-point="${move.x} ${move.y}"]`);
        field.classList.add(this._colorToClass[move.color]);

        this._fields.forEach(field => field.classList.remove('gp-heartbeat'));
        field.classList.add('gp-heartbeat');
    }

    /**
     * Updates the previous move, next move und follow moves button according to the state of
     * the number of the current move in view.
     */
    _updateNavigationButtons() {
        let isCurrentMoveTheLastMove = this._numberOfCurrentMoveInView === this._game.numberOfMoves();

        this._previousMoveButton.disabled = this._numberOfCurrentMoveInView === 0;
        this._nextMoveButton.disabled = isCurrentMoveTheLastMove;
        this._followMovesButton.disabled = isCurrentMoveTheLastMove;

        // Remove flashing if the user follow the moves.
        if (isCurrentMoveTheLastMove) {
            this._followMovesButton.classList.remove('btn-warning', 'gp-heartbeat');
        }
    }

    /**
     * Display the move only if the user looks at the last move.
     *
     * @param {{x:Number, y:Number, color:Number}} move
     */
    _onMoveAppendedToGame(move) {
        // Only show if the user follow the moves. Otherwise notify user that a new move is available.
        if (this._followMovesButton.disabled === true) {
            this._showMove(move);
            this._numberOfCurrentMoveInView++;
            this._updateNavigationButtons();
        } else {
            this._followMovesButton.classList.add('btn-warning', 'gp-heartbeat');
        }
    }

    _onFieldClick(event) {
        let cell = event.target;

        let loadingTimeout = setTimeout(() => this._gameNode.classList.add('gp-loading'), 250);

        service.move(this._game.gameId, cell.dataset.column)
            .catch(() => true)
            .finally(() => {
                if (loadingTimeout) clearTimeout(loadingTimeout);
                this._gameNode.classList.remove('gp-loading');
            });
    }

    _onPlayerMoved(event) {
        this._game.appendMove(event.detail);
    }

    _onPreviousMoveClick(event) {
        event.preventDefault();

        this._numberOfCurrentMoveInView--;
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onNextMoveClick(event) {
        event.preventDefault();

        this._numberOfCurrentMoveInView++;
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onFollowMovesClick(event) {
        event.preventDefault();

        this._numberOfCurrentMoveInView = this._game.numberOfMoves();
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _registerEventHandler() {
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
});
