import * as sse from '../Common/EventSource.js'

customElements.define('connect-four-running-games', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();

        sse.subscribe('lobby', {
            'ConnectFour.RunningGamesUpdated': e => this.innerText = e.detail.count
        }, this._sseAbortController.signal);
    }

    disconnectedCallback() {
        this._sseAbortController.abort();
    }
});
