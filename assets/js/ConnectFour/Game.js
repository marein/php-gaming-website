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

        this._playerId = this.getAttribute('player-id');
        this._previousMoveButton = document.querySelector(this.getAttribute('previous-move-selector'));
        this._nextMoveButton = document.querySelector(this.getAttribute('next-move-selector'));
        this._followMovesButton = document.querySelector(this.getAttribute('follow-moves-selector'));
        this._game = new GameModel(game);
        this._numberOfCurrentMoveInView = this._game.numberOfMoves();
        this._fields = this._gameNode.querySelectorAll('.gp-game__field');
        this._changeCurrentPlayer(game.currentPlayerId);
        this._forceFollowMovesAnimation = false;
        this._isMoveInProgress = false;

        this._showMovesUpTo(this._numberOfCurrentMoveInView);

        this._registerEventHandler();
    }

    disconnectedCallback() {
        this._onDisconnect.forEach(f => f());
    }

    /**
     * @param {String} playerId
     */
    _colorClass(playerId) {
        if (playerId === this._game.redPlayerId) return 'gp-game__field--red';
        if (playerId === this._game.yellowPlayerId) return 'gp-game__field--yellow';
        return null;
    }

    _previewClass() {
        if (this._game.redPlayerId === this._playerId) return 'gp-game__field--preview-red';
        if (this._game.yellowPlayerId === this._playerId) return 'gp-game__field--preview-yellow';
        return null;
    }

    /**
     * @param {String} playerId
     */
    _changeCurrentPlayer(playerId) {
        this._game.currentPlayerId = playerId;
        this._toggleInteractivity();
    }

    _toggleInteractivity() {
        const isCurrentPlayer = this._game.currentPlayerId === this._playerId;
        const isInHistoryMode = this._numberOfCurrentMoveInView !== this._game.numberOfMoves();

        this._gameNode.classList.toggle('gp-game--disabled', !isCurrentPlayer || isInHistoryMode);
    }

    /**
     * @param {Number} index
     */
    _showMovesUpTo(index) {
        this._fields.forEach(field => field.classList.remove(
            'gp-game__field--highlight', 'gp-game__field--current', 'gp-game__field--red', 'gp-game__field--yellow')
        );

        this._game.moves.slice(0, index).forEach(this._showMove.bind(this));
        this._updateNavigationButtons();
        this._showWinningSequences();
        this._toggleInteractivity();
    }

    /**
     * Display the move in the view.
     *
     * @param {import('./Model/Game.js').Move} move
     */
    _showMove(move) {
        let field = this._gameNode.querySelector(`.gp-game__field[data-column="${move.x}"][data-row="${move.y}"]`);
        field.classList.add(this._colorClass(move.playerId));

        this._fields.forEach(field => field.classList.remove('gp-game__field--highlight', 'gp-game__field--current'));
        field.classList.add('gp-game__field--highlight', 'gp-game__field--current');
    }

    /**
     * Updates the previous move, next move und follow moves button according to the state of
     * the number of the current move in view.
     */
    _updateNavigationButtons() {
        const isCurrentMoveTheLastMove = this._numberOfCurrentMoveInView === this._game.numberOfMoves();
        this._forceFollowMovesAnimation = isCurrentMoveTheLastMove ? false : this._forceFollowMovesAnimation;
        const isCurrentPlayer = this._game.currentPlayerId === this._playerId;
        const showAnimation = this._forceFollowMovesAnimation || (isCurrentPlayer && !isCurrentMoveTheLastMove);

        this._previousMoveButton.disabled = this._numberOfCurrentMoveInView === 0;
        this._nextMoveButton.disabled = isCurrentMoveTheLastMove;
        this._followMovesButton.disabled = isCurrentMoveTheLastMove;
        this._followMovesButton.classList.toggle('btn-warning', showAnimation);
        this._followMovesButton.classList.toggle('icon-tada', showAnimation);
    }

    _showWinningSequences() {
        if (this._game.winningSequences.length === 0) return;
        if (this._numberOfCurrentMoveInView !== this._game.numberOfMoves()) return;

        this._fields.forEach(field => field.classList.remove('gp-game__field--highlight'));
        this._game.winningSequences.forEach(winningSequence => {
            winningSequence.points.forEach(point => setTimeout(() => this._gameNode
                .querySelector(`.gp-game__field[data-column="${point.x}"][data-row="${point.y}"]`)
                .classList
                .add('gp-game__field--highlight'), Math.random() * 100)
            );
        });
    }

    /**
     * @param {Number} column
     * @returns {HTMLElement|undefined}
     */
    _lastFieldInColumn(column) {
        const fields = this._gameNode.querySelectorAll(
            `.gp-game__field[data-column="${column}"]:not(.gp-game__field--red):not(.gp-game__field--yellow)`
        );
        return fields[fields.length - 1];
    }

    _removeFieldPreview() {
        this._gameNode.querySelector(`.${this._previewClass()}`)?.classList.remove(this._previewClass());
    }

    _onFieldClick(event) {
        if (this._isMoveInProgress) return;

        this._isMoveInProgress = true;

        const field = this._lastFieldInColumn(event.target.dataset.column);
        if (!field) {
            this._isMoveInProgress = false;
            return;
        }

        const eventOptions = {
            bubbles: true,
            detail: {
                x: parseInt(field.dataset.column),
                y: parseInt(field.dataset.row),
                color: this._playerId === this._game.redPlayerId ? 1 : 2,
                playerId: this._playerId,
                nextPlayerId: this._playerId === this._game.redPlayerId
                    ? this._game.yellowPlayerId
                    : this._game.redPlayerId,
                preview: true
            }
        };
        this.dispatchEvent(new CustomEvent('ConnectFour.PlayerMoved', eventOptions));
        this._removeFieldPreview();

        const numberOfMoves = this._game.numberOfMoves();
        service.move(this._game.gameId, field.dataset.column)
            .catch(() => {
                if (numberOfMoves !== this._game.numberOfMoves()) return;

                this.dispatchEvent(new CustomEvent('ConnectFour.PlayerMovedFailed', eventOptions));
            })
            .finally(() => this._isMoveInProgress = false);
    }

    _onFieldMouseover(event) {
        if (this._isMoveInProgress) return;
        this._removeFieldPreview();

        this._lastFieldInColumn(event.target.dataset.column)?.classList.add(this._previewClass());
    }

    _onFieldMouseout(event) {
        if (this._isMoveInProgress) return;
        this._removeFieldPreview();
    }

    _onPlayerJoined(event) {
        this._game.redPlayerId = event.detail.redPlayerId;
        this._game.yellowPlayerId = event.detail.yellowPlayerId;
        this._changeCurrentPlayer(event.detail.redPlayerId);
    }

    _onPlayerMoved(event) {
        if (this._game.hasMove(event.detail)) return;

        if (!event.detail.preview) this._isMoveInProgress = false;
        if (this._followMovesButton.disabled === true) this._numberOfCurrentMoveInView++;

        this._game.appendMove(event.detail);
        this._changeCurrentPlayer(event.detail.nextPlayerId);
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onPlayerMovedFailed(event) {
        if (this._followMovesButton.disabled === true) this._numberOfCurrentMoveInView--;
        this._game.removeMove(event.detail);

        this._changeCurrentPlayer(event.detail.playerId);
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onGameWon(event) {
        this._game.winningSequences = event.detail.winningSequences;
        this._showWinningSequences();
        this._changeCurrentPlayer('');
        this._forceFollowMovesAnimation = this._numberOfCurrentMoveInView !== this._game.numberOfMoves();
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
        this.addEventListener('ConnectFour.PlayerMovedFailed', this._onPlayerMovedFailed.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.PlayerJoined', this._onPlayerJoined.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.PlayerMoved', this._onPlayerMoved.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.GameWon', this._onGameWon.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.GameDrawn', () => this._changeCurrentPlayer(''));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.GameAborted', () => this._changeCurrentPlayer(''));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.GameResigned', () => this._changeCurrentPlayer(''));

        this._fields.forEach(field => {
            field.addEventListener('click', this._onFieldClick.bind(this));
            field.addEventListener('mouseover', this._onFieldMouseover.bind(this));
            field.addEventListener('mouseout', this._onFieldMouseout.bind(this));
        });

        this._previousMoveButton.addEventListener('click', this._onPreviousMoveClick.bind(this));
        this._nextMoveButton.addEventListener('click', this._onNextMoveClick.bind(this));
        this._followMovesButton.addEventListener('click', this._onFollowMovesClick.bind(this));
    }
});
