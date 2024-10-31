import {service} from './GameService.js'
import 'confirmation-button'
import {html} from 'uhtml/node.js'

customElements.define('connect-four-resign-button', class extends HTMLElement {
    connectedCallback() {
        this.replaceChildren(html`
            <confirmation-button @confirmation-button:yes="${this._onConfirmed.bind(this)}">
                <button class="btn w-100">
                    ${this.innerHTML}
                </button>
            </confirmation-button>
        `);
    }

    _onConfirmed(e) {
        service.resign(this.getAttribute('game-id'))
            .then(() => true)
            .finally(() => e.target.reset());
    }
});
