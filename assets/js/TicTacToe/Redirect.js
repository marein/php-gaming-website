import * as sse from '../Common/EventSource.js'

customElements.define('tic-tac-toe-redirect', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();

        sse.subscribe(`ttt-challenge-${this.getAttribute('challenge-id')}`, {
            'TicTacToe.ChallengeAccepted': () => alert('Redirect to game.')
        }, this._sseAbortController.signal);
    }

    disconnectedCallback() {
        this._sseAbortController.abort();
    }
});
