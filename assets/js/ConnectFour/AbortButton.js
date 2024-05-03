import {service} from './GameService.js'

customElements.define('connect-four-abort-button', class extends HTMLElement {
    connectedCallback() {
        this._button = document.createElement('button');
        this._button.setAttribute('id', 'abort-game');
        this._button.classList.add('btn', 'btn-outline-danger', 'w-100');
        this._button.innerHTML = this.innerHTML;

        this.innerHTML = '';
        this.append(this._button);

        this._gameId = this.getAttribute('game-id');

        this._registerEventHandler();
    }

    _onButtonClick(event) {
        event.preventDefault();

        this._button.disabled = true;
        this._button.classList.add('btn-loading');

        service.abort(this._gameId)
            .then(() => true)
            .finally(() => {
                this._button.disabled = false;
                this._button.classList.remove('btn-loading');
            });
    }

    _registerEventHandler() {
        this._button.addEventListener('click', this._onButtonClick.bind(this));
    }
});
