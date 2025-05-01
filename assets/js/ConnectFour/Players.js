import {html} from 'uhtml/node.js';
import * as sse from '../Common/EventSource.js'

customElements.define('connect-four-players', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();

        this._appendPlayerBadges(this._redPlayerElement = this.querySelector('[data-red-player]'));
        this._appendPlayerBadges(this._yellowPlayerElement = this.querySelector('[data-yellow-player]'));

        this._render();

        if (!['open', 'running'].includes(this.getAttribute('game-state'))) return;

        window.addEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.addEventListener('ConnectFour.PlayerMovedFailed', this._onPlayerMovedFailed);
        sse.subscribe(`connect-four-${this.getAttribute('game-id')}`, {
            'ConnectFour.PlayerJoined': this._onPlayerJoined,
            'ConnectFour.PlayerMoved': this._onPlayerMoved,
            'ConnectFour.GameAborted': this._onGameAborted,
            'ConnectFour.GameWon': this._onGameWon,
            'ConnectFour.GameResigned': this._onGameResigned,
            'ConnectFour.GameDrawn': this._onGameDrawn
        }, this._sseAbortController.signal);
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
        if (e.detail.gameId !== this.getAttribute('game-id')) return;
        this.setAttribute('current-player-id', e.detail.redPlayerId);
        this.setAttribute('red-player-id', e.detail.redPlayerId);
        this.setAttribute('yellow-player-id', e.detail.yellowPlayerId);

        this._render();
    }

    _onPlayerMoved = e => {
        if (e.detail.gameId !== this.getAttribute('game-id')) return;
        this.setAttribute('current-player-id', e.detail.nextPlayerId);

        this._render();
    }

    _onPlayerMovedFailed = e => {
        if (e.detail.gameId !== this.getAttribute('game-id')) return;
        this.setAttribute('current-player-id', e.detail.playerId);

        this._render();
    }

    _onGameWon = e => {
        if (e.detail.gameId !== this.getAttribute('game-id')) return;
        this.setAttribute('current-player-id', '');
        this.setAttribute('winner-id', e.detail.winnerId);
        this.setAttribute('loser-id', e.detail.loserId);

        this._render();
        this._removeEventListeners();
    }

    _onGameAborted = e => {
        if (e.detail.gameId !== this.getAttribute('game-id')) return;
        this.setAttribute('current-player-id', '');
        this.setAttribute('aborted-by', e.detail.abortedPlayerId);

        this._render();
        this._removeEventListeners();
    }

    _onGameResigned = e => {
        if (e.detail.gameId !== this.getAttribute('game-id')) return;
        this.setAttribute('current-player-id', '');
        this.setAttribute('winner-id', e.detail.opponentPlayerId);
        this.setAttribute('resigned-by', e.detail.resignedPlayerId);

        this._render();
        this._removeEventListeners();
    }

    _onGameDrawn = e => {
        if (e.detail.gameId !== this.getAttribute('game-id')) return;
        this.setAttribute('current-player-id', '');
        this.setAttribute('game-state', 'draw');

        this._render();
        this._removeEventListeners();
    }

    _removeEventListeners() {
        window.removeEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.removeEventListener('ConnectFour.PlayerMovedFailed', this._onPlayerMovedFailed);
        this._sseAbortController.abort();
    }
});
