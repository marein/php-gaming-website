import {html} from 'uhtml/node.js';

customElements.define('connect-four-player-status', class extends HTMLElement {
    connectedCallback() {
        this.appendChild(html`
            <span class="badge bg-info-lt d-none" data-you>${this.getAttribute('text-you')}</span>
            <span class="badge bg-success-lt d-none" data-won>${this.getAttribute('text-won')}</span>
            <span class="badge bg-danger-lt d-none" data-lost>${this.getAttribute('text-lost')}</span>
            <span class="badge bg-danger-lt d-none" data-resigned>${this.getAttribute('text-resigned')}</span>
            <span class="badge bg-danger-lt d-none" data-aborted>${this.getAttribute('text-aborted')}</span>
        `);

        this._render();

        window.addEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.addEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.addEventListener('ConnectFour.GameAborted', this._onGameAborted);
        window.addEventListener('ConnectFour.GameWon', this._onGameWon);
        window.addEventListener('ConnectFour.GameResigned', this._onGameResigned);
        window.addEventListener('ConnectFour.GameDrawn', this._onGameFinished);
    }

    disconnectedCallback() {
        this._removeEventListeners();
    }

    _render() {
        const colorPlayerId = this.getAttribute('color-player-id');

        if (!colorPlayerId) return;

        const isYou = this.getAttribute('player-id') === colorPlayerId;
        const isCurrentPlayer = colorPlayerId === this.getAttribute('current-player-id');
        const isWinner = colorPlayerId === this.getAttribute('winner-id');
        const isLoser = colorPlayerId === this.getAttribute('loser-id');
        const hasResigned = colorPlayerId === this.getAttribute('resigned-by');
        const hasAborted = colorPlayerId === this.getAttribute('aborted-by');

        this.querySelector('.status-dot')?.classList.toggle('status-dot-animated', isCurrentPlayer);
        this.querySelector('[data-username]')?.classList.toggle('fw-bold', isCurrentPlayer);
        this.querySelector('[data-you]').classList.toggle('d-none', !isYou);
        this.querySelector('[data-won]').classList.toggle('d-none', !isWinner);
        this.querySelector('[data-lost]').classList.toggle('d-none', !isLoser);
        this.querySelector('[data-resigned]').classList.toggle('d-none', !hasResigned);
        this.querySelector('[data-aborted]').classList.toggle('d-none', !hasAborted);
    }

    _onPlayerJoined = e => {
        const isRed = this.getAttribute('color') === 'red';

        this.setAttribute('color-player-id', isRed ? e.detail.redPlayerId : e.detail.yellowPlayerId);
        this.setAttribute('current-player-id', isRed ? e.detail.redPlayerId : '');

        this._render();
    }

    _onPlayerMoved = e => {
        this.setAttribute('current-player-id', e.detail.nextPlayerId);

        this._render();
    }

    _onGameWon = e => {
        this.setAttribute('current-player-id', '');
        this.setAttribute('winner-id', e.detail.winnerId);
        this.setAttribute('loser-id', e.detail.loserId);

        this._render();
        this._removeEventListeners();
    }

    _onGameAborted = e => {
        this.setAttribute('current-player-id', '');
        this.setAttribute('aborted-by', e.detail.abortedPlayerId);

        this._render();
        this._removeEventListeners();
    }

    _onGameResigned = e => {
        this.setAttribute('current-player-id', '');
        this.setAttribute('winner-id', e.detail.opponentPlayerId);
        this.setAttribute('resigned-by', e.detail.resignedPlayerId);

        this._render();
        this._removeEventListeners();
    }

    _onGameFinished = e => {
        this.setAttribute('current-player-id', '');

        this._render();
        this._removeEventListeners();
    }

    _removeEventListeners() {
        window.removeEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.removeEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.removeEventListener('ConnectFour.GameWon', this._onGameWon);
        window.removeEventListener('ConnectFour.GameAborted', this._onGameFinished);
        window.removeEventListener('ConnectFour.GameResigned', this._onGameResigned);
        window.removeEventListener('ConnectFour.GameDrawn', this._onGameFinished);
    }
});
