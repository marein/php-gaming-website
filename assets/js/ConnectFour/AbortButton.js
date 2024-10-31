import {service} from './GameService.js'
import 'confirmation-button'
import {html} from 'uhtml/node.js'

customElements.define('connect-four-abort-button', class extends HTMLElement {
    connectedCallback() {
        this.replaceChildren(html`
            <confirmation-button @confirmation-button:yes="${this._onConfirmed.bind(this)}">
                <button id="abort-game" class="btn btn-outline-danger w-100">
                    ${this.innerHTML}
                </button>
            </confirmation-button>
        `);
    }

    _onConfirmed(e) {
        service.abort(this.getAttribute('game-id'))
            .then(() => true)
            .finally(() => e.target.reset());
    }
});
