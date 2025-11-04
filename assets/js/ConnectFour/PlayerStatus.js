import {html} from 'uhtml/node.js';
import * as sse from '../Common/EventSource.js'

customElements.define('connect-four-player-status', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();

        this.replaceChildren(
            this._currentPlayerElement = html`
                <span data-title="Player's turn" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon icon-tada">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M2 6m0 2a2 2 0 0 1 2 -2h16a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-16a2 2 0 0 1 -2 -2z"/>
                        <path d="M6 12h4m-2 -2v4"/>
                        <path d="M15 11l0 .01"/>
                        <path d="M18 13l0 .01"/>
                    </svg>
                </span>`,
            this._wonElement = html`
                <span data-title="Won" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M8 21l8 0"/>
                        <path d="M12 17l0 4"/>
                        <path d="M7 4l10 0"/>
                        <path d="M17 4v8a5 5 0 0 1 -10 0v-8"/>
                        <path d="M5 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                        <path d="M19 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                    </svg>
                </span>`,
            this._lostElement = html`
                <span data-title="Lost" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M8 21h8"/>
                        <path d="M12 17v4"/>
                        <path d="M8 4h9"/>
                        <path d="M17 4v8c0 .31 -.028 .612 -.082 .905m-1.384 2.632a5 5 0 0 1 -8.534 -3.537v-5"/>
                        <path d="M5 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                        <path d="M19 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                        <path d="M3 3l18 18"/>
                    </svg>
                </span>`,
            this._drawnElement = html`
                <span data-title="Drawn" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 10h14"/>
                        <path d="M5 14h14"/>
                    </svg>
                </span>`,
            this._resignedElement = html`
                <span data-title="Resigned" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 5a5 5 0 0 1 7 0a5 5 0 0 0 7 0v9a5 5 0 0 1 -7 0a5 5 0 0 0 -7 0v-9z"/>
                        <path d="M5 21v-7"/>
                    </svg>
                </span>`,
            this._timedOutElement = html`
                <span data-title="Timed out" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M8.56 3.69a9 9 0 0 0 -2.92 1.95"/>
                        <path d="M3.69 8.56a9 9 0 0 0 -.69 3.44"/>
                        <path d="M3.69 15.44a9 9 0 0 0 1.95 2.92"/>
                        <path d="M8.56 20.31a9 9 0 0 0 3.44 .69"/>
                        <path d="M15.44 20.31a9 9 0 0 0 2.92 -1.95"/>
                        <path d="M20.31 15.44a9 9 0 0 0 .69 -3.44"/>
                        <path d="M20.31 8.56a9 9 0 0 0 -1.95 -2.92"/>
                        <path d="M15.44 3.69a9 9 0 0 0 -3.44 -.69"/>
                        <path d="M10 10v4a2 2 0 1 0 4 0v-4a2 2 0 1 0 -4 0z"/>
                    </svg>
                </span>`,
            this._abortedElement = html`
                <span data-title="Aborted" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
                        <path d="M10 10l4 4m0 -4l-4 4"/>
                    </svg>
                </span>`,
            this._youElement = html`
                <span data-title="You" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/>
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                    </svg>
                </span>`
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
            'ConnectFour.GameTimedOut': this._onGameTimedOut,
            'ConnectFour.GameDrawn': this._onGameDrawn
        }, this._sseAbortController.signal);
    }

    disconnectedCallback() {
        this._removeEventListeners();
    }

    _render() {
        const playerId = this.getAttribute('player-id');
        if (!playerId) return;

        this._currentPlayerElement.classList.toggle('d-none', playerId !== this.getAttribute('current-player-id'));
        this._youElement.classList.toggle('d-none', playerId !== this.getAttribute('you-id'));
        this._wonElement.classList.toggle('d-none', playerId !== this.getAttribute('winner-id'));
        this._lostElement.classList.toggle('d-none', playerId !== this.getAttribute('loser-id'));
        this._drawnElement.classList.toggle('d-none', this.getAttribute('game-state') !== 'draw');
        this._resignedElement.classList.toggle('d-none', playerId !== this.getAttribute('resigned-by'));
        this._timedOutElement.classList.toggle('d-none', playerId !== this.getAttribute('timed-out-by'));
        this._abortedElement.classList.toggle('d-none', playerId !== this.getAttribute('aborted-by'));
    }

    _onPlayerJoined = e => {
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

    _onGameTimedOut = e => {
        this.setAttribute('current-player-id', '');
        this.setAttribute('winner-id', e.detail.opponentPlayerId);
        this.setAttribute('timed-out-by', e.detail.timedOutPlayerId);

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
        window.removeEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.removeEventListener('ConnectFour.PlayerMovedFailed', this._onPlayerMovedFailed);
        this._sseAbortController.abort();
    }
});
