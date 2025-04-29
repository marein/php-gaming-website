import {service} from './GameService.js'
import * as sse from '../Common/EventSource.js'

customElements.define('connect-four-redirect', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();

        sse.subscribe(`connect-four-${this.getAttribute('game-id')}`, {
            'ConnectFour.PlayerJoined': e => service.redirectTo(e.detail.gameId)
        }, this._sseAbortController.signal);
    }

    disconnectedCallback() {
        this._sseAbortController.abort();
    }
});
