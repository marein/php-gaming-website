import {service} from './GameService.js'
import {Game as GameModel} from './Model/Game.js'
import {html} from 'uhtml/node.js'

function play(sheet) {
    import('https://cdn.jsdelivr.net/gh/marein/js-scriptune@main/src/scriptune.js')
        .then(m => m.play(sheet));
}

const sounds = {
    error: () => play(`-:s F2:s C2:e`),
    move: () => play(`#BPM 300
    C4:s C5:s`),
    next: () => sounds.move(),
    previous: () => play(`#BPM 300
    C5:s C4:s`),
    win: () => play(`-:s C4:s E4:s G4:s C5:e G4:s C5:e`),
    loss: () => play(`#BPM 180
    -:s C4:s E4:s G4:s C5:e -:s C5:s -:s C5:s -:e C1:h`),
    join: () => play(`C4:s E4:s G4:s C5:e`)
};

customElements.define('connect-four-game', class extends HTMLElement {
    connectedCallback() {
        let game = JSON.parse(this.getAttribute('game'));

        this.append(this._gameNode = html`
            <div style="${`--grid-cols: ${game.width}`}"
                 class="gp-cf-game">${[...Array(game.height * game.width).keys()].map(n => html`
                <div class="gp-cf-game__field"
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
        this._fields = this._gameNode.querySelectorAll('.gp-cf-game__field');
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
        window.removeEventListener('ConnectFour.GameDrawn', this._onGameDrawn);
        window.removeEventListener('ConnectFour.GameAborted', this._onGameAborted);
        window.removeEventListener('ConnectFour.GameResigned', this._onGameResigned);
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

        this._previousMoveButton.disabled = this._numberOfCurrentMoveInView === 0;
        this._nextMoveButton.disabled = isCurrentMoveTheLastMove;
        this._followMovesButton.disabled = isCurrentMoveTheLastMove;
        this._followMovesButton.classList.toggle('btn-warning', showAnimation);
        this._followMovesButton.classList.toggle('icon-tada', showAnimation);
    }

    _showWinningSequences() {
        if (this._game.winningSequences.length === 0) return;
        if (this._numberOfCurrentMoveInView !== this._game.numberOfMoves()) return;

        this._fields.forEach(field => field.classList.remove('gp-cf-game__field--highlight'));
        this._game.winningSequences.forEach(winningSequence => {
            winningSequence.points.forEach((point, i) => setTimeout(
                () => this._fieldByPoint(point).classList.add('gp-cf-game__field--highlight'),
                i * 100
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
            sounds.error();
            this._isMoveInProgress = false;
            return;
        }

        sounds.move();

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
        this._removePreviewToken();

        service.move(this._game.gameId, field.dataset.column)
            .then(() => this._game.hasPendingMove(eventOptions.detail) && this._removePendingToken())
            .catch(() => {
                if (!this._game.hasPendingMove(eventOptions.detail)) return;

                sounds.error();
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
        sounds.join();
        this._game.redPlayerId = event.detail.redPlayerId;
        this._game.yellowPlayerId = event.detail.yellowPlayerId;
        this._changeCurrentPlayer(event.detail.redPlayerId);
    }

    _onPlayerMoved = event => {
        this._changeCurrentPlayer(event.detail.nextPlayerId);
        if (this._game.hasPendingMove(event.detail)) this._removePendingToken();
        if (this._game.hasMove(event.detail)) return;
        if (event.detail.playerId !== this._playerId) sounds.move();

        if (!event.detail.pending) this._isMoveInProgress = false;
        if (this._followMovesButton.disabled === true) this._numberOfCurrentMoveInView++;

        this._game.appendMove(event.detail);
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onPlayerMovedFailed(event) {
        if (this._followMovesButton.disabled === true) this._numberOfCurrentMoveInView--;
        this._game.removeMove(event.detail);

        this._changeCurrentPlayer(event.detail.playerId);
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onGameWon = event => {
        if (event.detail.loserId === this._playerId) sounds.loss();
        if (event.detail.winnerId === this._playerId) sounds.win();
        this._game.winningSequences = event.detail.winningSequences;
        this._showWinningSequences();
        this._changeCurrentPlayer('');
        this._forceFollowMovesAnimation = this._numberOfCurrentMoveInView !== this._game.numberOfMoves();
    }

    _onGameDrawn = () => {
        sounds.win();
        this._changeCurrentPlayer('');
    }

    _onGameResigned = event => {
        if (event.detail.resignedPlayerId === this._playerId) sounds.loss();
        if (event.detail.opponentPlayerId === this._playerId) sounds.win();
        this._changeCurrentPlayer('');
    }

    _onGameAborted = () => {
        sounds.error();
        this._changeCurrentPlayer('');
    }

    _onPreviousMoveClick(event) {
        event.preventDefault();

        sounds.previous();
        this._numberOfCurrentMoveInView--;
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onNextMoveClick(event) {
        event.preventDefault();

        sounds.next();
        this._numberOfCurrentMoveInView++;
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _onFollowMovesClick(event) {
        event.preventDefault();

        sounds.next();
        this._numberOfCurrentMoveInView = this._game.numberOfMoves();
        this._showMovesUpTo(this._numberOfCurrentMoveInView);
    }

    _registerEventHandler() {
        this.addEventListener('ConnectFour.PlayerMovedFailed', this._onPlayerMovedFailed.bind(this));

        window.addEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.addEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.addEventListener('ConnectFour.GameWon', this._onGameWon);
        window.addEventListener('ConnectFour.GameDrawn', this._onGameDrawn);
        window.addEventListener('ConnectFour.GameAborted', this._onGameAborted);
        window.addEventListener('ConnectFour.GameResigned', this._onGameResigned);

        this._fields.forEach(field => {
            field.addEventListener('click', this._onFieldClick.bind(this));
            field.addEventListener('mouseenter', this._onFieldMouseenter.bind(this));
            field.addEventListener('mouseleave', this._onFieldMouseleave.bind(this));
        });

        this._previousMoveButton.addEventListener('click', this._onPreviousMoveClick.bind(this));
        this._nextMoveButton.addEventListener('click', this._onNextMoveClick.bind(this));
        this._followMovesButton.addEventListener('click', this._onFollowMovesClick.bind(this));
    }
});
