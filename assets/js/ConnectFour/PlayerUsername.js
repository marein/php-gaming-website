import * as sse from '../Common/EventSource.js'

customElements.define('connect-four-player-username', class extends HTMLElement {
    connectedCallback() {
        if (!['open', 'running'].includes(this.getAttribute('game-state'))) return;

        this._sseAbortController = new AbortController();

        sse.subscribe(`connect-four-${this.getAttribute('game-id')}`, {
            'ConnectFour.PlayerJoined': this._onPlayerJoined
        }, this._sseAbortController.signal);
    }

    disconnectedCallback() {
        this._sseAbortController.abort();
    }

    _onPlayerJoined = e => {
        if (e.detail.gameId !== this.getAttribute('game-id')) return;

        this.innerText = this.getAttribute('color') === 'yellow'
            ? (e.detail.yellowPlayerUsername ?? 'Anonymous')
            : (e.detail.redPlayerUsername ?? 'Anonymous');

        this._sseAbortController.abort();
    }
});
