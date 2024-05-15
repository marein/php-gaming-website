import {service} from './GameService.js'
import {html} from 'uhtml/node.js'

customElements.define('connect-four-open-button', class extends HTMLElement {
    connectedCallback() {
        this._onDisconnect = [];
        this._button = html`
            <button id="abort-game" class="btn btn-primary w-100" data-open-game-button>${this.innerHTML}</button>
        `;

        this.replaceChildren(this._button);

        this._currentOpenGameId = '';

        this._button.addEventListener('click', this._onButtonClick.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.PlayerJoined', this._onPlayerJoined.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.GameAborted', this._onGameAborted.bind(this));
    }

    disconnectedCallback() {
        this._onDisconnect.forEach(f => f());
    }

    async _onButtonClick(event) {
        event.preventDefault();

        this._button.disabled = true;
        this._button.classList.add('btn-loading');

        if (this._currentOpenGameId) {
            service.abort(this._currentOpenGameId);
        }

        service.open().then((game) => {
            this._currentOpenGameId = game.gameId;
            this._button.disabled = false;
            this._button.classList.remove('btn-loading');
        }).catch(() => {
            this._button.disabled = false;
            this._button.classList.remove('btn-loading');
        });
    }

    _onPlayerJoined(event) {
        if (this._currentOpenGameId === event.detail.gameId) {
            service.redirectTo(this._currentOpenGameId);
        }
    }

    _onGameAborted(event) {
        if (this._currentOpenGameId === event.detail.gameId) {
            this._currentOpenGameId = '';
        }
    }
});
