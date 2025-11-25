import {html} from 'uhtml/node.js';
import * as sse from '../Common/EventSource.js'
import * as serverTime from '../Common/ServerTime.js';
import * as scriptune from 'https://cdn.jsdelivr.net/gh/marein/js-scriptune@main/src/scriptune.js'

customElements.define('connect-four-timer', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();
        this._playerId = this.getAttribute('player-id');
        this._remainingMs = parseInt(this.getAttribute('remaining-ms'));
        this._turnEndsAt = parseInt(this.getAttribute('turn-ends-at'));
        this._panicLevelOneBelowMs = parseInt(this.getAttribute('panic-one-ms-below') || 10000);
        this._panicLevelTwoBelowMs = parseInt(this.getAttribute('panic-two-ms-below') || 7000);
        this._panicLevelThreeBelowMs = parseInt(this.getAttribute('panic-three-ms-below') || 3000);
        this._currentTickSound = null;

        window.requestAnimationFrame(this._render);

        sse.subscribe(`connect-four-${this.getAttribute('game-id')}`, {
            'ConnectFour.PlayerJoined': this._onPlayerJoined,
            'ConnectFour.PlayerMoved': this._onPlayerMoved,
            'ConnectFour.GameTimedOut': this._onGameTimedOut,
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
        const remainingMs = this._turnEndsAt
            ? Math.max(0, this._turnEndsAt - serverTime.now())
            : this._remainingMs;

        const remainingSeconds = Math.floor(remainingMs / 1000);
        const hours = Math.floor(remainingSeconds / 3600).toString().padStart(2, '0');
        const minutes = Math.floor((remainingSeconds % 3600) / 60).toString().padStart(2, '0');
        const seconds = (remainingSeconds % 60).toString().padStart(2, '0');
        const milliseconds = Math.floor(remainingMs % 1000 / 100);
        const isPanicLevelOne = remainingMs > 0 && remainingMs < this._panicLevelOneBelowMs;
        const isPanicLevelTwo = remainingMs > 0 && remainingMs < this._panicLevelTwoBelowMs;
        const isPanicLevelThree = remainingMs > 0 && remainingMs < this._panicLevelThreeBelowMs;

        this.replaceChildren(
            hours > 0
                ? html`${hours}:${minutes}:${seconds}`
                : html`${minutes}:${seconds}${isPanicLevelOne ? html`<sup>${milliseconds}</sup>` : ''}`
        );

        if (isPanicLevelTwo && this._currentTickSound !== remainingSeconds) {
            this._currentTickSound = remainingSeconds;
            const volume = Math.min(
                1,
                Math.round((.2 + (1 - (remainingMs / this._panicLevelOneBelowMs)) * 0.9) * 10) / 10
            );
            scriptune.play(`#BPM 300\n#VOLUME ${volume}\nC5:s ${isPanicLevelThree ? '-:s C5:s' : ''}`)
        }

        window.requestAnimationFrame(this._render);
    }

    _onPlayerJoined = e => {
        this._playerId = this.getAttribute('color') === 'yellow' ? e.detail.yellowPlayerId : e.detail.redPlayerId;
        this._remainingMs = e.detail.redPlayerId === this._playerId
            ? e.detail.redPlayerRemainingMs
            : e.detail.yellowPlayerRemainingMs;
        this._turnEndsAt = e.detail.redPlayerId === this._playerId
            ? e.detail.redPlayerTurnEndsAt
            : null;
    }

    _onPlayerMoved = e => {
        this._currentTickSound = null;
        this._remainingMs = e.detail.playerId === this._playerId
            ? e.detail.playerRemainingMs
            : this._remainingMs;
        this._turnEndsAt = e.detail.nextPlayerId === this._playerId
            ? e.detail.nextPlayerTurnEndsAt
            : null;
    }

    _onGameTimedOut = e => {
        this._turnEndsAt = null;
        this._remainingMs = e.detail.timedOutPlayerId === this._playerId ? 0 : this._remainingMs;
    }

    _onFinished = e => {
        this._turnEndsAt = null;
    }
});
