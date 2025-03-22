import {html} from 'uhtml/node.js';

customElements.define('connect-four-player-status', class extends HTMLElement {
    connectedCallback() {
        this._render();

        window.addEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.addEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.addEventListener('ConnectFour.GameWon', this._onGameWon);
        window.addEventListener('ConnectFour.GameAborted', this._onGameFinished);
        window.addEventListener('ConnectFour.GameResigned', this._onGameResigned);
        window.addEventListener('ConnectFour.GameDrawn', this._onGameFinished);
    }

    disconnectedCallback() {
        window.removeEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.removeEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.removeEventListener('ConnectFour.GameWon', this._onGameWon);
        window.removeEventListener('ConnectFour.GameAborted', this._onGameFinished);
        window.removeEventListener('ConnectFour.GameResigned', this._onGameResigned);
        window.removeEventListener('ConnectFour.GameDrawn', this._onGameFinished);
    }

    _render() {
        if (!this.getAttribute('player-id')) return;

        this.innerHTML = '';

        const isCurrentPlayer = this.getAttribute('player-id') === this.getAttribute('current-player-id');
        const isWinningPlayer = this.getAttribute('player-id') === this.getAttribute('winning-player-id');
        const isLosingPLayer = this.getAttribute('winning-player-id') !== '' && !isWinningPlayer;

        if (isCurrentPlayer) {
            this.replaceChildren(html`
                <span class="${`badge bg-${this.getAttribute('color')}-lt`}">
                    ${this.getAttribute('text-turn')}
                </span>
            `);
        } else if (isWinningPlayer) {
            this.replaceChildren(html`
                <span class="badge bg-success-lt">${this.getAttribute('text-won')}</span>
            `);
        } else if (isLosingPLayer) {
            this.replaceChildren(html`
                <span class="badge bg-danger-lt">${this.getAttribute('text-lost')}</span>
            `);
        }
    }

    _onPlayerJoined = e => {
        if (this.getAttribute('color') === 'red') {
            this.setAttribute('player-id', e.detail.startingPlayerId);
            this.setAttribute('current-player-id', e.detail.startingPlayerId);
        } else if (e.detail.startingPlayerId !== e.detail.opponentPlayerId) {
            this.setAttribute('player-id', e.detail.opponentPlayerId);
        } else if (e.detail.startingPlayerId !== e.detail.joinedPlayerId) {
            this.setAttribute('player-id', e.detail.joinedPlayerId);
        }

        this._render();
    }

    _onPlayerMoved = e => {
        this.setAttribute('current-player-id', e.detail.nextPlayerId);
        this._render();
    }

    _onGameWon = e => {
        this.setAttribute('current-player-id', '');
        this.setAttribute('winning-player-id', e.detail.winnerPlayerId);
        this._render();
    }

    _onGameResigned = e => {
        this.setAttribute('current-player-id', '');
        this.setAttribute('winning-player-id', e.detail.opponentPlayerId);
        this._render();
    }

    _onGameFinished = e => {
        this.remove();
    }
});
