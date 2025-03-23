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
        const isWinningPlayer = colorPlayerId === this.getAttribute('winning-player-id');
        const isLosingPLayer = colorPlayerId === this.getAttribute('losing-player-id');
        const isResigningPlayer = colorPlayerId === this.getAttribute('resigning-player-id');
        const isAbortingPlayer = colorPlayerId === this.getAttribute('aborting-player-id');

        this.querySelector('.status-dot')?.classList.toggle('status-dot-animated', isCurrentPlayer);
        this.querySelector('[data-username]')?.classList.toggle('fw-bold', isCurrentPlayer);
        this.querySelector('[data-you]').classList.toggle('d-none', !isYou);
        this.querySelector('[data-won]').classList.toggle('d-none', !isWinningPlayer);
        this.querySelector('[data-lost]').classList.toggle('d-none', !isLosingPLayer);
        this.querySelector('[data-resigned]').classList.toggle('d-none', !isResigningPlayer);
        this.querySelector('[data-aborted]').classList.toggle('d-none', !isAbortingPlayer);
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
        this.setAttribute('winning-player-id', e.detail.winningPlayerId);
        this.setAttribute('losing-player-id', e.detail.losingPlayerId);

        this._render();
        this._removeEventListeners();
    }

    _onGameAborted = e => {
        this.setAttribute('current-player-id', '');
        this.setAttribute('aborting-player-id', e.detail.abortedPlayerId);

        this._render();
        this._removeEventListeners();
    }

    _onGameResigned = e => {
        this.setAttribute('current-player-id', '');
        this.setAttribute('winning-player-id', e.detail.opponentPlayerId);
        this.setAttribute('resigning-player-id', e.detail.resignedPlayerId);

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
