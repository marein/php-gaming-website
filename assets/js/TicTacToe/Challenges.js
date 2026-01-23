import {html} from 'uhtml/node.js'
import * as sse from '../Common/EventSource.js'
import {createUsernameNode} from '../Identity/utils.js'

/**
 * @typedef {{challengeId: String, width: Number, height: Number, playerId: String, playerUsername: String}} OpenChallenge
 */

customElements.define('tic-tac-toe-challenges', class extends HTMLElement {
    async connectedCallback() {
        this._sseAbortController = new AbortController();

        this.append(html`
            <div class="card">
                <table class="table table-nowrap user-select-none cursor-default card-table">
                    <thead>
                    <tr>
                        <th class="w-75">Player</th>
                        <th>Size</th>
                        <th>Rating</th>
                    </tr>
                    </thead>
                    ${this._challenges = html`<tbody class="cursor-pointer border-0"></tbody>`}
                </table>
            </div>
        `);

        this._playerId = this.getAttribute('player-id');
        this._maximumNumberOfChallengesInList = parseInt(this.getAttribute('maximum-number-of-challenges'));
        this._pendingOpenChallenges = new Map();
        this._scheduleRenderTimeout = null;
        this._useScheduleRenderAfter = Date.now() + 750;
        const usernames = JSON.parse(this.getAttribute('usernames'));
        JSON.parse(this.getAttribute("open-challenges")).forEach(challenge => {
            this._pendingOpenChallenges.set(
                challenge.challengeId,
                {...challenge, playerUsername: usernames[challenge.playerId]}
            );
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
            this._challenges.classList.add('gp-loading');
            await new Promise(r => setTimeout(r, 250));
        }

        this._challenges.querySelectorAll('[data-deleted]').forEach(row => row.remove());

        let count = this._challenges.children.length;
        for (const [challengeId, openChallenge] of this._pendingOpenChallenges) {
            if (count >= this._maximumNumberOfChallengesInList) break;
            this._challenges.appendChild(this._createChallengeNode(openChallenge));
            this._pendingOpenChallenges.delete(challengeId);
            count++;
        }

        this._challenges.classList.remove('gp-loading');
    }

    _scheduleRender = () => {
        if (this._scheduleRenderTimeout) return;
        this._scheduleRenderTimeout = setTimeout(() => this._render(true), 3000);
    }

    /**
     * @param {OpenChallenge} openChallenge
     * @returns {Node}
     */
    _createChallengeNode = openChallenge => {
        const row = html`
            <tr data="${openChallenge}"
                class="${this._playerId === openChallenge.playerId ? 'table-success' : 'table-light'}">
                <td>${createUsernameNode(openChallenge.playerUsername)}</td>
                <td>${openChallenge.width} x ${openChallenge.height}</td>
                <td></td>
            </tr>
        `;

        row.addEventListener('click', event => {
            event.preventDefault();
            if (row.classList.contains('table-secondary') || row.closest('.gp-loading')) return;

            row.classList.add('table-secondary', 'cursor-default');
            row.classList.remove('table-success', 'table-light');

            if (this._playerId === openChallenge.playerId) {
                const url = this.getAttribute('withdraw-url').replace('CHALLENGE_ID', openChallenge.challengeId);
                fetch(url, {method: 'POST'})
                    .then(() => true)
                    .catch(() => this._removeChallenge(openChallenge.challengeId));
            } else {
                const url = this.getAttribute('accept-url').replace('CHALLENGE_ID', openChallenge.challengeId);
                fetch(url, {method: 'POST'})
                    .then(() => alert('Redirect to game.'))
                    .catch(() => this._removeChallenge(openChallenge.challengeId));
            }
        });

        return row;
    }

    _removeChallenge = challengeId => {
        this._pendingOpenChallenges.delete(challengeId);

        const row = this.querySelector('[data-challenge-id="' + challengeId + '"]');
        if (!row) return;

        row.dataset.deleted = 'true';
        row.classList.add('table-secondary', 'cursor-default');
        row.classList.remove('table-success', 'table-light');

        Date.now() > this._useScheduleRenderAfter ? this._scheduleRender() : this._render(false);
    }

    _onChallengeOpened = event => {
        const openChallenge = {
            challengeId: event.detail.challengeId,
            width: event.detail.width,
            height: event.detail.height,
            playerId: event.detail.playerId,
            playerUsername: event.detail.playerUsername
        };

        if (this._challenges.querySelector(`[data-challenge-id="${openChallenge.challengeId}"]`)) return;

        if (this._challenges.children.length < this._maximumNumberOfChallengesInList) {
            this._challenges.appendChild(this._createChallengeNode(openChallenge));
        } else {
            this._pendingOpenChallenges.set(openChallenge.challengeId, openChallenge);
        }
    }

    _onChallengeAcceptedOrWithdrawn = event => this._removeChallenge(event.detail.challengeId);

    _onUserArrived = event => {
        this._playerId = event.detail.userId;

        this.querySelectorAll(`[data-player-id="${this._playerId}"]`)
            .forEach(challenge => challenge.classList.replace('table-light', 'table-success'));
    }

    _registerEventHandler() {
        window.addEventListener('WebInterface.UserArrived', this._onUserArrived);
        sse.subscribe('ttt-lobby', {
            'TicTacToe.ChallengeOpened': this._onChallengeOpened,
            'TicTacToe.ChallengeAccepted': this._onChallengeAcceptedOrWithdrawn,
            'TicTacToe.ChallengeWithdrawn': this._onChallengeAcceptedOrWithdrawn
        }, this._sseAbortController.signal);
    }
});
