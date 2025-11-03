import * as sse from '../Common/EventSource.js'
import {createUsernameNode} from '../Identity/utils.js'

customElements.define('connect-four-player-username', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();

        this.replaceChildren(createUsernameNode(this.innerText));

        if (!['open'].includes(this.getAttribute('game-state'))) return;

        sse.subscribe(`connect-four-${this.getAttribute('game-id')}`, {
            'ConnectFour.PlayerJoined': this._onPlayerJoined
        }, this._sseAbortController.signal);
    }

    disconnectedCallback() {
        this._sseAbortController.abort();
    }

    _onPlayerJoined = e => {
        this.replaceChildren(
            createUsernameNode(
                this.getAttribute('color') === 'yellow'
                    ? e.detail.yellowPlayerUsername
                    : e.detail.redPlayerUsername
            )
        );

        this._sseAbortController.abort();
    }
});
