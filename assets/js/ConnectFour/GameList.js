import {service} from './GameService.js'
import {html} from 'uhtml/node.js'
import * as sse from '../Common/EventSource.js'
import {createUsernameNode} from '../Identity/utils.js'

/**
 * @typedef {{gameId: String, playerId: String, playerUsername: String}} OpenGame
 */

customElements.define('connect-four-game-list', class extends HTMLElement {
    async connectedCallback() {
        this._sseAbortController = new AbortController();

        this.append(html`
            <div class="card">
                <table class="table table-nowrap user-select-none cursor-default card-table">
                    <thead>
                    <tr>
                        <th class="w-75">Player</th>
                        <th>Rating</th>
                    </tr>
                    </thead>
                    ${this._games = html`<tbody class="cursor-pointer border-0"></tbody>`}
                </table>
            </div>
        `);

        this._playerId = this.getAttribute('player-id');
        this._maximumNumberOfGamesInList = parseInt(this.getAttribute('maximum-number-of-games'));
        this._pendingOpenGames = new Map();
        this._scheduleRenderTimeout = null;
        this._useScheduleRenderAfter = Date.now() + 750;
        const usernames = JSON.parse(this.getAttribute('usernames'));
        JSON.parse(this.getAttribute("open-games")).forEach(game => {
            this._pendingOpenGames.set(game.gameId, {...game, playerUsername: usernames[game.playerId]});
        });

        await this._render(false);
        this._registerEventHandler();
    }

    disconnectedCallback() {
        window.removeEventListener('WebInterface.UserArrived', this._onUserArrived);
        this._sseAbortController.abort();
    }

    _render = async withLoading => {
        this._scheduleRenderTimeout = null;
        if (withLoading) {
            this._games.classList.add('gp-loading');
            await new Promise(r => setTimeout(r, 250));
        }

        this._games.querySelectorAll('[data-deleted]').forEach(row => row.remove());

        let count = this._games.children.length;
        for (const [gameId, openGame] of this._pendingOpenGames) {
            if (count >= this._maximumNumberOfGamesInList) break;
            this._games.appendChild(this._createGameNode(openGame));
            this._pendingOpenGames.delete(gameId);
            count++;
        }

        this._games.classList.remove('gp-loading');
    }

    _scheduleRender = () => {
        if (this._scheduleRenderTimeout) return;
        this._scheduleRenderTimeout = setTimeout(() => this._render(true), 3000);
    }

    /**
     * @param {OpenGame} openGame
     * @returns {Node}
     */
    _createGameNode = openGame => {
        const row = html`
            <tr data="${openGame}"
                class="${this._playerId === openGame.playerId ? 'table-success' : 'table-light'}">
                <td>${createUsernameNode(openGame.playerUsername)}</td>
                <td></td>
            </tr>
        `;

        row.addEventListener('click', event => {
            event.preventDefault();
            if (row.classList.contains('table-secondary') || row.closest('.gp-loading')) return;

            row.classList.add('table-secondary', 'cursor-default');
            row.classList.remove('table-success', 'table-light');

            if (this._playerId === openGame.playerId) {
                service.abort(openGame.gameId)
                    .then(() => true)
                    .catch(() => this._removeGame(openGame.gameId));
            } else {
                service.join(openGame.gameId)
                    .then(() => service.redirectTo(openGame.gameId))
                    .catch(() => this._removeGame(openGame.gameId));
            }
        });

        return row;
    }

    _removeGame = gameId => {
        this._pendingOpenGames.delete(gameId);

        const row = this.querySelector('[data-game-id="' + gameId + '"]');
        if (!row) return;

        row.dataset.deleted = 'true';
        row.classList.add('table-secondary', 'cursor-default');
        row.classList.remove('table-success', 'table-light');

        Date.now() > this._useScheduleRenderAfter ? this._scheduleRender() : this._render(false);
    }

    _onGameOpened = event => {
        const openGame = {
            gameId: event.detail.gameId,
            playerId: event.detail.playerId,
            playerUsername: event.detail.playerUsername
        };

        if (this._games.querySelector(`[data-game-id="${openGame.gameId}"]`)) return;

        if (this._games.children.length < this._maximumNumberOfGamesInList) {
            this._games.appendChild(this._createGameNode(openGame));
        } else {
            this._pendingOpenGames.set(openGame.gameId, openGame);
        }
    }

    _onPlayerJoinedOrGameAborted = event => this._removeGame(event.detail.gameId);

    _onUserArrived = event => {
        this._playerId = event.detail.userId;

        this.querySelectorAll(`[data-player-id="${this._playerId}"]`)
            .forEach(game => game.classList.replace('table-light', 'table-success'));
    }

    _registerEventHandler() {
        window.addEventListener('WebInterface.UserArrived', this._onUserArrived);
        sse.subscribe('lobby', {
            'ConnectFour.GameOpened': this._onGameOpened,
            'ConnectFour.PlayerJoined': this._onPlayerJoinedOrGameAborted,
            'ConnectFour.GameAborted': this._onPlayerJoinedOrGameAborted
        }, this._sseAbortController.signal);
    }
});
