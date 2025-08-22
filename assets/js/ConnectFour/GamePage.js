import * as sse from '../Common/EventSource.js'

customElements.define('connect-four-game-page', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();

        this._handlePageTitle();
    }

    disconnectedCallback() {
        this._sseAbortController.abort();
    }

    _handlePageTitle() {
        if (!['open'].includes(this.getAttribute('game-state'))) return;

        sse.subscribe(`connect-four-${this.getAttribute('game-id')}`, {
            'ConnectFour.PlayerJoined': e => {
                if (e.detail.gameId !== this.getAttribute('game-id')) return;

                document.title = document.title.replace(
                    '? vs. ?',
                    `${e.detail.redPlayerUsername ?? 'Anonymous'} vs. ${e.detail.yellowPlayerUsername ?? 'Anonymous'}`
                );

                this._sseAbortController.abort();
            }
        }, this._sseAbortController.signal);
    }
});
