import {service} from './GameService.js'

customElements.define('connect-four-open-button', class extends HTMLElement {
    connectedCallback() {
        this._onDisconnect = [];
        this._button = document.createElement('button');
        this._button.classList.add('button');
        this._button.setAttribute('data-open-game-button', '');
        this._button.innerHTML = this.innerHTML;

        this.innerHTML = '';
        this.append(this._button);

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

    _onButtonClick(event) {
        event.preventDefault();

        this._button.disabled = true;
        this._button.classList.add('loading-indicator');

        if (this._currentOpenGameId) {
            service.abort(this._currentOpenGameId);
        }

        service.open().then((game) => {
            this._currentOpenGameId = game.gameId;
            this._button.disabled = false;
            this._button.classList.remove('loading-indicator');
        }).catch(() => {
            this._button.disabled = false;
            this._button.classList.remove('loading-indicator');
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
