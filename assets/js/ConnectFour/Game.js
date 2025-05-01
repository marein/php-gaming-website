import {service} from './GameService.js'
import {Game as GameModel} from './Model/Game.js'
import {html} from 'uhtml/node.js'
import * as sse from '../Common/EventSource.js'

customElements.define('connect-four-game', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();
        let game = JSON.parse(this.getAttribute('game'));

        this.replaceChildren(this._gameNode = html`
            <div style="${`--grid-cols: ${game.width}`}"
                 class="gp-cf-game">${[...Array(game.height * game.width).keys()].map(n => html`
                <div class="gp-cf-game__field"
                     data-column="${(n % game.width) + 1}"
                     data-row="${Math.floor(n / game.width) + 1}">
                </div>`)}
            </div>
        `);

        this._playerId = this.getAttribute('player-id');
        this._previousMoveButton = this.hasAttribute('previous-move-selector')
            ? document.querySelector(this.getAttribute('previous-move-selector'))
            : null;
        this._nextMoveButton = this.hasAttribute('next-move-selector')
            ? document.querySelector(this.getAttribute('next-move-selector'))
            : null;
        this._followMovesButton = this.hasAttribute('follow-moves-selector')
            ? document.querySelector(this.getAttribute('follow-moves-selector'))
            : null;
        this._game = new GameModel(game);
        this._numberOfCurrentMoveInView = this._game.numberOfMoves();
        this._fields = this._gameNode.querySelectorAll('.gp-cf-game__field');
        this._changeCurrentPlayer(game.currentPlayerId);
        this._forceFollowMovesAnimation = false;
        this._isMoveInProgress = false;

        this._showMovesUpTo(this._numberOfCurrentMoveInView);

        this._registerEventHandler();
    }

    disconnectedCallback() {
        this._sseAbortController.abort();
    }

    /**
     * @param {1|2} color
     * @param {Boolean} pending
     * @param {Boolean} preview
     * @returns {HTMLElement}
     */
    _createTokenNode(color, pending = false, preview = false) {
        const colorClass = color === 2 ? ' gp-cf-token--yellow' : '';
        const pendingClass = pending ? ' gp-cf-token--pending' : '';
        const previewClass = preview ? ' gp-cf-token--preview' : '';

        return html`<span class="${`gp-cf-token${colorClass}${pendingClass}${previewClass}`}"></span>`;
    }

    /**
     * @param {String} playerId
     */
    _changeCurrentPlayer(playerId) {
        this._game.currentPlayerId = playerId;
        this._toggleInteractivity();
    }

    _removePendingToken() {
        this._game.pendingMove = null;
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _toggleInteractivity() {
        const isCurrentPlayer = this._game.currentPlayerId === this._playerId;
        const isInHistoryMode = this._numberOfCurrentMoveInView !== this._game.numberOfMoves();

        this._gameNode.classList.toggle('gp-cf-game--disabled', !isCurrentPlayer || isInHistoryMode);
    }

    /**
     * @param {Number} index
     */
    _showMovesUpTo(index) {
        this._fields.forEach(field => {
            field.innerHTML = '';
            field.classList.remove('gp-cf-game__field--highlight', 'gp-cf-game__field--current')
        });

        this._game.moves.slice(0, index).forEach((move, i) => {
            const field = this._fieldByPoint(move);
            field.append(this._createTokenNode(move.color, this._game.hasPendingMove(move)));
            if (i === index - 1) field.classList.add('gp-cf-game__field--highlight', 'gp-cf-game__field--current');
        });

        this._updateNavigationButtons();
        this._showWinningSequences();
        this._toggleInteractivity();
    }

    _updateNavigationButtons() {
        let isCurrentMoveTheLastMove = this._numberOfCurrentMoveInView === this._game.numberOfMoves();
        this._forceFollowMovesAnimation = isCurrentMoveTheLastMove ? false : this._forceFollowMovesAnimation;
        const isCurrentPlayer = this._game.currentPlayerId === this._playerId;
        const showAnimation = this._forceFollowMovesAnimation || (isCurrentPlayer && !isCurrentMoveTheLastMove);

        if (this._previousMoveButton) this._previousMoveButton.disabled = this._numberOfCurrentMoveInView === 0;
        if (this._nextMoveButton) this._nextMoveButton.disabled = isCurrentMoveTheLastMove;
        if (this._followMovesButton) {
            this._followMovesButton.disabled = isCurrentMoveTheLastMove;
            this._followMovesButton.classList.toggle('btn-warning', showAnimation);
            this._followMovesButton.classList.toggle('icon-tada', showAnimation);
        }
    }

    _showWinningSequences() {
        if (this._game.winningSequences.length === 0) return;
        if (this._numberOfCurrentMoveInView !== this._game.numberOfMoves()) return;

        this._fields.forEach(field => field.classList.remove('gp-cf-game__field--highlight'));
        this._game.winningSequences.forEach(winningSequence => {
            winningSequence.points.forEach(point => setTimeout(
                () => this._fieldByPoint(point).classList.add('gp-cf-game__field--highlight'),
                0
            ));
        });
    }

    /**
     * @param {import('./Model/Game.js').Move|import('./Model/Game.js').Point} point
     * @returns {HTMLElement|undefined}
     */
    _fieldByPoint(point) {
        return this._gameNode.querySelector(`.gp-cf-game__field[data-column="${point.x}"][data-row="${point.y}"]`);
    }

    /**
     * @param {Number} column
     * @returns {HTMLElement|undefined}
     */
    _lastFieldInColumn(column) {
        return Array.from(this._gameNode.querySelectorAll(
            `.gp-cf-game__field[data-column="${column}"]`
        )).findLast(field => field.querySelector('.gp-cf-token:not(.gp-cf-token--preview)') === null);
    }

    _removePreviewToken() {
        this._gameNode.querySelector('.gp-cf-token--preview')?.remove();
    }

    _onFieldClick(event) {
        if (this._isMoveInProgress) return;

        this._isMoveInProgress = true;

        const field = this._lastFieldInColumn(event.target.closest('[data-column]').dataset.column);
        if (!field) {
            this._isMoveInProgress = false;
            return;
        }

        const eventOptions = {
            bubbles: true,
            detail: {
                gameId: this._game.gameId,
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
        this._removePreviewToken();

        service.move(this._game.gameId, field.dataset.column)
            .then(() => this._game.hasPendingMove(eventOptions.detail) && this._removePendingToken())
            .catch(() => {
                if (!this._game.hasPendingMove(eventOptions.detail)) return;

                this.dispatchEvent(new CustomEvent('ConnectFour.PlayerMovedFailed', eventOptions));
            })
            .finally(() => this._isMoveInProgress = false);
    }

    _onFieldMouseenter(event) {
        if (this._isMoveInProgress) return;
        this._removePreviewToken();

        this._lastFieldInColumn(event.target.dataset.column)?.append(
            this._createTokenNode(this._playerId === this._game.redPlayerId ? 1 : 2, false, true)
        );
    }

    _onFieldMouseleave() {
        if (this._isMoveInProgress) return;
        this._removePreviewToken();
    }

    _onPlayerJoined = event => {
        if (event.detail.gameId !== this._game.gameId) return;
        this._game.redPlayerId = event.detail.redPlayerId;
        this._game.yellowPlayerId = event.detail.yellowPlayerId;
        this._changeCurrentPlayer(event.detail.redPlayerId);
    }

    _onPlayerMoved = event => {
        if (event.detail.gameId !== this._game.gameId) return;
        this._changeCurrentPlayer(event.detail.nextPlayerId);
        if (this._game.hasPendingMove(event.detail)) this._removePendingToken();
        if (this._game.hasMove(event.detail)) return;

        if (!event.detail.pending) this._isMoveInProgress = false;
        if (!this._followMovesButton || this._followMovesButton.disabled === true) this._numberOfCurrentMoveInView++;

        this._game.appendMove(event.detail);
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onPlayerMovedFailed(event) {
        if (event.detail.gameId !== this._game.gameId) return;
        if (!this._followMovesButton || this._followMovesButton.disabled === true) this._numberOfCurrentMoveInView--;
        this._game.removeMove(event.detail);

        this._changeCurrentPlayer(event.detail.playerId);
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onGameWon = event => {
        if (event.detail.gameId !== this._game.gameId) return;
        this._game.winningSequences = event.detail.winningSequences;
        this._showWinningSequences();
        this._changeCurrentPlayer('');
        this._forceFollowMovesAnimation = this._numberOfCurrentMoveInView !== this._game.numberOfMoves();
    }

    _onGameFinished = event => {
        if (event.detail.gameId !== this._game.gameId) return;
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
        this.addEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);

        this._fields.forEach(field => {
            field.addEventListener('click', this._onFieldClick.bind(this));
            field.addEventListener('mouseenter', this._onFieldMouseenter.bind(this));
            field.addEventListener('mouseleave', this._onFieldMouseleave.bind(this));
        });

        this._previousMoveButton?.addEventListener('click', this._onPreviousMoveClick.bind(this));
        this._nextMoveButton?.addEventListener('click', this._onNextMoveClick.bind(this));
        this._followMovesButton?.addEventListener('click', this._onFollowMovesClick.bind(this));

        if (!['open', 'running'].includes(this._game.state)) return;

        sse.subscribe(`connect-four-${this._game.gameId}`, {
            'ConnectFour.PlayerJoined': this._onPlayerJoined,
            'ConnectFour.PlayerMoved': this._onPlayerMoved,
            'ConnectFour.GameWon': this._onGameWon,
            'ConnectFour.GameDrawn': this._onGameFinished,
            'ConnectFour.GameAborted': this._onGameFinished,
            'ConnectFour.GameResigned': this._onGameFinished
        }, this._sseAbortController.signal);
    }
});
