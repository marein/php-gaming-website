import {service} from './GameService.js'
import {Game as GameModel} from './Model/Game.js'
import {html} from 'uhtml/node.js'

customElements.define('connect-four-game', class extends HTMLElement {
    connectedCallback() {
        this._onDisconnect = [];

        let game = JSON.parse(this.getAttribute('game'));

        this.append(this._gameNode = html`
            <div style="${`--grid-cols: ${game.width}`}"
                 class="gp-game">${[...Array(game.height * game.width).keys()].map(n => html`
                <div class="gp-game__field"
                     data-column="${(n % game.width) + 1}"
                     data-row="${Math.floor(n / game.width) + 1}">
                </div>`)}
            </div>
        `);

        this._previousMoveButton = document.querySelector(this.getAttribute('previous-move-selector'));
        this._nextMoveButton = document.querySelector(this.getAttribute('next-move-selector'));
        this._followMovesButton = document.querySelector(this.getAttribute('follow-moves-selector'));
        this._game = new GameModel(game);
        this._numberOfCurrentMoveInView = this._game.numberOfMoves();
        this._fields = this._gameNode.querySelectorAll('.gp-game__field');
        this._colorToClass = {1: 'bg-red', 2: 'bg-yellow'};

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
        this._fields.forEach(field => field.classList.remove(
            'gp-game__field--highlight', 'gp-game__field--current', 'bg-red', 'bg-yellow')
        );

        this._game.moves.slice(0, index).forEach(this._showMove.bind(this));
        this._updateNavigationButtons();
        this._showWinningSequences();
    }

    /**
     * Display the move in the view.
     *
     * @param {import('./Model/Game.js').Move} move
     */
    _showMove(move) {
        let field = this._gameNode.querySelector(`.gp-game__field[data-column="${move.x}"][data-row="${move.y}"]`);
        field.classList.add(this._colorToClass[move.color]);

        this._fields.forEach(field => field.classList.remove('gp-game__field--highlight', 'gp-game__field--current'));
        field.classList.add('gp-game__field--highlight', 'gp-game__field--current');
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
            this._followMovesButton.classList.remove('btn-warning', 'icon-tada');
        }
    }

    _showWinningSequences() {
        if (this._game.winningSequences.length === 0) return;
        if (this._numberOfCurrentMoveInView !== this._game.numberOfMoves()) return;

        this._fields.forEach(field => field.classList.remove('gp-game__field--highlight'));
        this._game.winningSequences.forEach(winningSequence => {
            winningSequence.points.forEach(point => this._gameNode
                .querySelector(`.gp-game__field[data-column="${point.x}"][data-row="${point.y}"]`)
                .classList
                .add('gp-game__field--highlight')
            );
        });
    }

    /**
     * Only show if the user follows the moves. Otherwise, notify user that a new move is available.
     *
     * @param {import('./Model/Game.js').Move} move
     */
    _onMoveAppendedToGame(move) {
        if (this._followMovesButton.disabled === true) {
            this._showMove(move);
            this._numberOfCurrentMoveInView++;
            this._updateNavigationButtons();
            this._showWinningSequences();
        } else {
            this._followMovesButton.classList.add('btn-warning', 'icon-tada');
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

    _onFieldMouseover(event) {
        this._removeFieldHover();

        const fields = this._gameNode.querySelectorAll(
            `.gp-game__field[data-column="${event.target.dataset.column}"]:not(.bg-red):not(.bg-yellow)`
        );
        fields[fields.length - 1]?.classList.add('gp-game__field--hover');
    }

    _removeFieldHover() {
        this._gameNode.querySelector(`.gp-game__field--hover`)?.classList.remove('gp-game__field--hover');
    }

    _onPlayerMoved(event) {
        this._game.appendMove({
            x: event.detail.x,
            y: event.detail.y,
            color: event.detail.color,
        });
    }

    _onGameWon(event) {
        this._game.winningSequences = event.detail.winningSequences;
        this._showWinningSequences();
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

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.GameWon', this._onGameWon.bind(this));

        this._fields.forEach(field => {
            field.addEventListener('click', this._onFieldClick.bind(this));
            field.addEventListener('mouseover', this._onFieldMouseover.bind(this));
            field.addEventListener('mouseout', this._removeFieldHover.bind(this));
        });

        this._previousMoveButton.addEventListener('click', this._onPreviousMoveClick.bind(this));
        this._nextMoveButton.addEventListener('click', this._onNextMoveClick.bind(this));
        this._followMovesButton.addEventListener('click', this._onFollowMovesClick.bind(this));
    }
});
