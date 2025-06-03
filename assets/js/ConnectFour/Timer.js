import {html} from 'uhtml/node.js';
import * as sse from '../Common/EventSource.js'
import * as serverTime from '../Common/ServerTime.js';

customElements.define('connect-four-timer', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();
        this._gameId = this.getAttribute('game-id');
        this._color = this.getAttribute('color') || 'red';
        this._playerId = this.getAttribute('player-id');
        this._remainingMs = parseInt(this.getAttribute('remaining-ms'));
        this._turnEndsAt = this.getAttribute('turn-ends-at');

        this.replaceChildren(this._timerNode = html`<span class="h2"></span>`);

        window.requestAnimationFrame(this._render);

        sse.subscribe(`connect-four-${this._gameId}`, {
            'ConnectFour.PlayerJoined': this._onPlayerJoined,
            'ConnectFour.PlayerMoved': this._onPlayerMoved,
            'ConnectFour.GameAborted': this._onFinished,
            'ConnectFour.GameWon': this._onFinished,
            'ConnectFour.GameResigned': this._onFinished,
            'ConnectFour.GameDrawn': this._onFinished
        }, this._sseAbortController.signal);
    }

    disconnectedCallback() {
        this._sseAbortController.abort();
    }

    _render = () => {
        let remainingMs = this._turnEndsAt
            ? Math.max(0, new Date(this._turnEndsAt) - serverTime.now())
            : this._remainingMs;

        const hours = Math.floor(remainingMs / (1000 * 60 * 60)).toString().padStart(2, '0');
        const minutes = Math.floor((remainingMs % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
        const seconds = Math.floor((remainingMs % (1000 * 60)) / 1000).toString().padStart(2, '0');

        this._timerNode.innerText = hours > 0
            ? `${hours}:${minutes}:${seconds}`
            : `${minutes}:${seconds}`;

        window.requestAnimationFrame(this._render);
    }

    _onPlayerJoined = e => {
        if (e.detail.gameId !== this._gameId) return;

        this._playerId = this._color === 'red' ? e.detail.redPlayerId : e.detail.yellowPlayerId;
        this._remainingMs = e.detail.redPlayerId === this._playerId
            ? e.detail.redPlayerRemainingMs
            : e.detail.yellowPlayerRemainingMs;
    }

    _onPlayerMoved = e => {
        if (e.detail.gameId !== this._gameId) return;

        this._remainingMs = e.detail.playerId === this._playerId
            ? e.detail.playerRemainingMs
            : this._remainingMs;
        this._turnEndsAt = e.detail.nextPlayerId === this._playerId
            ? e.detail.nextPlayerTurnEndsAt
            : null;
    }

    _onFinished = e => {
        if (e.detail.gameId !== this._gameId) return;

        this._turnEndsAt = '';
    }
});
