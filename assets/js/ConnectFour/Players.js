import {html} from 'uhtml/node.js';

customElements.define('connect-four-players', class extends HTMLElement {
    connectedCallback() {
        this._appendPlayerBadges(this._redPlayerElement = this.querySelector('[data-red-player]'));
        this._appendPlayerBadges(this._yellowPlayerElement = this.querySelector('[data-yellow-player]'));

        this._render();

        window.addEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.addEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.addEventListener('ConnectFour.PlayerMovedFailed', this._onPlayerMovedFailed);
        window.addEventListener('ConnectFour.GameAborted', this._onGameAborted);
        window.addEventListener('ConnectFour.GameWon', this._onGameWon);
        window.addEventListener('ConnectFour.GameResigned', this._onGameResigned);
        window.addEventListener('ConnectFour.GameDrawn', this._onGameDrawn);
    }

    disconnectedCallback() {
        this._removeEventListeners();
    }

    /**
     * @param {HTMLElement|null} playerElement
     */
    _appendPlayerBadges(playerElement) {
        playerElement?.appendChild(html`
            <span class="badge bg-info-lt d-none" data-you>${this.getAttribute('text-you')}</span>
            <span class="badge bg-success-lt d-none" data-won>${this.getAttribute('text-won')}</span>
            <span class="badge bg-danger-lt d-none" data-lost>${this.getAttribute('text-lost')}</span>
            <span class="badge bg-danger-lt d-none" data-resigned>${this.getAttribute('text-resigned')}</span>
            <span class="badge bg-danger-lt d-none" data-aborted>${this.getAttribute('text-aborted')}</span>
            <span class="badge bg-info-lt d-none" data-draw>${this.getAttribute('text-draw')}</span>
        `);
    }

    _render() {
        this._renderForPlayer(this._redPlayerElement, this.getAttribute('red-player-id'));
        this._renderForPlayer(this._yellowPlayerElement, this.getAttribute('yellow-player-id'));
    }

    /**
     * @param {HTMLElement|null} playerElement
     * @param {String} playerId
     */
    _renderForPlayer(playerElement, playerId) {
        if (!playerId) return;

        const isYou = playerId === this.getAttribute('player-id');
        const isCurrentPlayer = playerId === this.getAttribute('current-player-id');
        const isWinner = playerId === this.getAttribute('winner-id');
        const isLoser = playerId === this.getAttribute('loser-id');
        const hasResigned = playerId === this.getAttribute('resigned-by');
        const hasAborted = playerId === this.getAttribute('aborted-by');
        const isDraw = this.getAttribute('game-state') === 'draw';

        playerElement?.querySelector('.status-dot')?.classList.toggle('status-dot-animated', isCurrentPlayer);
        playerElement?.querySelector('[data-username]')?.classList.toggle('fw-bold', isCurrentPlayer);
        playerElement?.querySelector('[data-you]').classList.toggle('d-none', !isYou);
        playerElement?.querySelector('[data-won]').classList.toggle('d-none', !isWinner);
        playerElement?.querySelector('[data-lost]').classList.toggle('d-none', !isLoser);
        playerElement?.querySelector('[data-resigned]').classList.toggle('d-none', !hasResigned);
        playerElement?.querySelector('[data-aborted]').classList.toggle('d-none', !hasAborted);
        playerElement?.querySelector('[data-draw]').classList.toggle('d-none', !isDraw);
    }

    _onPlayerJoined = e => {
        this.setAttribute('current-player-id', e.detail.redPlayerId);
        this.setAttribute('red-player-id', e.detail.redPlayerId);
        this.setAttribute('yellow-player-id', e.detail.yellowPlayerId);

        this._render();
    }

    _onPlayerMoved = e => {
        this.setAttribute('current-player-id', e.detail.nextPlayerId);

        this._render();
    }

    _onPlayerMovedFailed = e => {
        this.setAttribute('current-player-id', e.detail.playerId);

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

    _onGameDrawn = e => {
        this.setAttribute('current-player-id', '');
        this.setAttribute('game-state', 'draw');

        this._render();
        this._removeEventListeners();
    }

    _removeEventListeners() {
        window.removeEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.removeEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.removeEventListener('ConnectFour.PlayerMovedFailed', this._onPlayerMovedFailed);
        window.removeEventListener('ConnectFour.GameWon', this._onGameWon);
        window.removeEventListener('ConnectFour.GameAborted', this._onGameAborted);
        window.removeEventListener('ConnectFour.GameResigned', this._onGameResigned);
        window.removeEventListener('ConnectFour.GameDrawn', this._onGameDrawn);
    }
});
