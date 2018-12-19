import { service } from './GameService.js'

class OpenGameButtonElement extends HTMLElement
{
    connectedCallback()
    {
        this._button = document.createElement('button');
        this._button.classList.add('button');
        this._button.setAttribute('data-open-game-button', '');
        this._button.innerHTML = this.innerHTML;

        this.innerHTML = '';
        this.append(this._button);

        this._gameId = this.getAttribute('game-id');
        this._currentOpenGameId = '';

        this._registerEventHandler();
    }

    _onButtonClick(event)
    {
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

    _onPlayerJoined(event)
    {
        if (this._currentOpenGameId === event.detail.gameId) {
            service.redirectTo(this._currentOpenGameId);
        }
    }

    _onGameAborted(event)
    {
        if (this._currentOpenGameId === event.detail.gameId) {
            this._currentOpenGameId = '';
        }
    }

    _registerEventHandler()
    {
        this._button.addEventListener('click', this._onButtonClick.bind(this));

        window.addEventListener(
            'ConnectFour.PlayerJoined',
            this._onPlayerJoined.bind(this)
        );

        window.addEventListener(
            'ConnectFour.GameAborted',
            this._onGameAborted.bind(this)
        );
    }
}

customElements.define('open-game-button', OpenGameButtonElement);
