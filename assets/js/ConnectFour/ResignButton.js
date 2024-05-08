import {service} from './GameService.js'
import {html} from 'uhtml/node.js'

customElements.define('connect-four-resign-button', class extends HTMLElement {
    connectedCallback() {
        this._button = html`
            <button id="abort-game" class="btn w-100">${this.innerHTML}</button>
        `;

        this.replaceChildren(this._button);

        this._gameId = this.getAttribute('game-id');

        this._registerEventHandler();
    }

    _onButtonClick(event) {
        event.preventDefault();

        this._button.disabled = true;
        this._button.classList.add('btn-loading');

        service.resign(this._gameId)
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
