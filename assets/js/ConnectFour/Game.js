import {service} from './GameService.js'
import {Game as GameModel} from './Model/Game.js'
import {html} from 'uhtml/node.js'

customElements.define('connect-four-game', class extends HTMLElement {
    connectedCallback() {
        let game = JSON.parse(this.getAttribute('game'));

        this.append(this._gameNode = html`
            <div style="${`--grid-cols: ${game.width}`}"
                 class="gp-game">${[...Array(game.height * game.width).keys()].map(n => html`
                <div class="gp-game__field"
                     data-column="${(n % game.width) + 1}"
                     data-row="${Math.floor(n / game.width) + 1}">
                    <span class="gp-game__field-hitbox"></span>
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
        window.removeEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.removeEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.removeEventListener('ConnectFour.GameWon', this._onGameWon);
        window.removeEventListener('ConnectFour.GameDrawn', this._onGameFinished);
        window.removeEventListener('ConnectFour.GameAborted', this._onGameFinished);
        window.removeEventListener('ConnectFour.GameResigned', this._onGameFinished);
    }

    /**
     * @param {Number} color
     * @param {Boolean} isPending
     */
    _colorClass(color, isPending) {
        const prefix = isPending ? 'gp-game__field--pending-' : 'gp-game__field--';

        return color === 1 ? `${prefix}red` : `${prefix}yellow`;
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

    _removePendingMove() {
        this._game.pendingMove = null;
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
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
            'gp-game__field--highlight', 'gp-game__field--current', 'gp-game__field--red', 'gp-game__field--yellow',
            'gp-game__field--pending-red', 'gp-game__field--pending-yellow')
        );

        this._game.moves.slice(0, index).forEach((move, i) => {
            const field = this._fieldByPoint(move);
            field.classList.add(this._colorClass(move.color, this._game.hasPendingMove(move)));
            if (i === index - 1) field.classList.add('gp-game__field--highlight', 'gp-game__field--current');
        });

        this._updateNavigationButtons();
        this._showWinningSequences();
        this._toggleInteractivity();
    }

    /**
     * Updates the previous move, next move und follow moves button according to the state of
     * the number of the current move in view.
     */
    _updateNavigationButtons() {
        let isCurrentMoveTheLastMove = this._numberOfCurrentMoveInView === this._game.numberOfMoves();
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
            winningSequence.points.forEach((point, i) => setTimeout(
                () => this._fieldByPoint(point).classList.add('gp-game__field--highlight'),
                i * 100
            ));
        });
    }

    /**
     * @param {import('./Model/Game.js').Move|import('./Model/Game.js').Point} point
     * @returns {HTMLElement|undefined}
     */
    _fieldByPoint(point) {
        return this._gameNode.querySelector(`.gp-game__field[data-column="${point.x}"][data-row="${point.y}"]`);
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

        const column = event.target.closest('[data-column]').dataset.column;
        const field = this._lastFieldInColumn(column);
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
                pending: true
            }
        };
        this.dispatchEvent(new CustomEvent('ConnectFour.PlayerMoved', eventOptions));
        this._removeFieldPreview();

        service.move(this._game.gameId, field.dataset.column)
            .then(() => this._game.hasPendingMove(eventOptions.detail) && this._removePendingMove())
            .catch(() => {
                if (!this._game.hasPendingMove(eventOptions.detail)) return;

                this.dispatchEvent(new CustomEvent('ConnectFour.PlayerMovedFailed', eventOptions));
            })
            .finally(() => this._isMoveInProgress = false);
    }

    _onFieldMouseover(event) {
        if (this._isMoveInProgress) return;
        this._removeFieldPreview();

        const column = event.target.closest('[data-column]').dataset.column;
        this._lastFieldInColumn(column)?.classList.add(this._previewClass());
    }

    _onFieldMouseout(event) {
        if (this._isMoveInProgress) return;
        this._removeFieldPreview();
    }

    _onPlayerJoined = event => {
        this._game.redPlayerId = event.detail.redPlayerId;
        this._game.yellowPlayerId = event.detail.yellowPlayerId;
        this._changeCurrentPlayer(event.detail.redPlayerId);
    }

    _onPlayerMoved = event => {
        if (this._game.hasPendingMove(event.detail)) this._removePendingMove();
        if (this._game.hasMove(event.detail)) return;

        if (!event.detail.pending) this._isMoveInProgress = false;
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

    _onGameWon = event => {
        this._game.winningSequences = event.detail.winningSequences;
        this._showWinningSequences();
        this._changeCurrentPlayer('');
        this._forceFollowMovesAnimation = this._numberOfCurrentMoveInView !== this._game.numberOfMoves();
    }

    _onGameFinished = () => {
        this._changeCurrentPlayer('');
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

        window.addEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.addEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.addEventListener('ConnectFour.GameWon', this._onGameWon);
        window.addEventListener('ConnectFour.GameDrawn', this._onGameFinished);
        window.addEventListener('ConnectFour.GameAborted', this._onGameFinished);
        window.addEventListener('ConnectFour.GameResigned', this._onGameFinished);

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
