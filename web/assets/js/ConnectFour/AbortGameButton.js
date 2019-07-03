import { service } from './GameService.js'

class AbortGameButtonElement extends HTMLElement
{
    connectedCallback()
    {
        this._button = document.createElement('button');
        this._button.setAttribute('id', 'abort-game');
        this._button.classList.add('button');
        this._button.innerHTML = this.innerHTML;

        this.innerHTML = '';
        this.append(this._button);

        this._gameId = this.getAttribute('game-id');

        this._registerEventHandler();
    }

    _onButtonClick(event)
    {
        event.preventDefault();

        this._button.disabled = true;
        this._button.classList.add('loading-indicator');

        service.abort(this._gameId).then(() => {
            this._button.disabled = false;
            this._button.classList.remove('loading-indicator');
        }).catch(() => {
            // todo: Handle exception based on error.
            this._button.disabled = false;
            this._button.classList.remove('loading-indicator');
        });
    }

    _registerEventHandler()
    {
        this._button.addEventListener('click', this._onButtonClick.bind(this));
    }
}

customElements.define('abort-game-button', AbortGameButtonElement);
