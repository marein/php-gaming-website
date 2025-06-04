import {html} from 'uhtml/node.js';
import * as sse from '../Common/EventSource.js'

customElements.define('connect-four-player-status', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();

        this.replaceChildren(
            this._currentPlayerElement = html`
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon icon-tada d-none">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M2 6m0 2a2 2 0 0 1 2 -2h16a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-16a2 2 0 0 1 -2 -2z"/>
                    <path d="M6 12h4m-2 -2v4"/>
                    <path d="M15 11l0 .01"/>
                    <path d="M18 13l0 .01"/>
                </svg>`,
            this._wonElement = html`
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon d-none">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M8 21l8 0"/>
                    <path d="M12 17l0 4"/>
                    <path d="M7 4l10 0"/>
                    <path d="M17 4v8a5 5 0 0 1 -10 0v-8"/>
                    <path d="M5 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                    <path d="M19 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                </svg>`,
            this._lostElement = html`
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon d-none">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M8 21h8"/>
                    <path d="M12 17v4"/>
                    <path d="M8 4h9"/>
                    <path d="M17 4v8c0 .31 -.028 .612 -.082 .905m-1.384 2.632a5 5 0 0 1 -8.534 -3.537v-5"/>
                    <path d="M5 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                    <path d="M19 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                    <path d="M3 3l18 18"/>
                </svg>`,
            this._drawElement = html`
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon d-none">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M5 10h14"/>
                    <path d="M5 14h14"/>
                </svg>`,
            this._resignedElement = html`
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon d-none">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M5 5a5 5 0 0 1 7 0a5 5 0 0 0 7 0v9a5 5 0 0 1 -7 0a5 5 0 0 0 -7 0v-9z"/>
                    <path d="M5 21v-7"/>
                </svg>`,
            this._abortedElement = html`
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon d-none">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
                    <path d="M10 10l4 4m0 -4l-4 4"/>
                </svg>`,
            this._youElement = html`
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="icon d-none">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/>
                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                </svg>`
        );

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

    _render() {
        const playerId = this.getAttribute('player-id');
        if (!playerId) return;

        const isYou = playerId === this.getAttribute('you-id');
        const isCurrentPlayer = playerId === this.getAttribute('current-player-id');
        const isWinner = playerId === this.getAttribute('winner-id');
        const isLoser = playerId === this.getAttribute('loser-id');
        const hasResigned = playerId === this.getAttribute('resigned-by');
        const hasAborted = playerId === this.getAttribute('aborted-by');
        const isDraw = this.getAttribute('game-state') === 'draw';

        this._currentPlayerElement.classList.toggle('d-none', !isCurrentPlayer);
        this._youElement.classList.toggle('d-none', !isYou);
        this._wonElement.classList.toggle('d-none', !isWinner);
        this._lostElement.classList.toggle('d-none', !isLoser);
        this._drawElement.classList.toggle('d-none', !isDraw);
        this._resignedElement.classList.toggle('d-none', !hasResigned);
        this._abortedElement.classList.toggle('d-none', !hasAborted);
    }

    _onPlayerJoined = e => {
        if (e.detail.gameId !== this.getAttribute('game-id')) return;

        this.setAttribute(
            'player-id',
            this.getAttribute('color') === 'yellow' ? e.detail.yellowPlayerId : e.detail.redPlayerId
        );
        this.setAttribute('current-player-id', e.detail.redPlayerId);

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
