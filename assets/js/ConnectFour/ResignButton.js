import {service} from './GameService.js'
import 'confirmation-button'
import {html} from 'uhtml/node.js'

customElements.define('connect-four-resign-button', class extends HTMLElement {
    connectedCallback() {
        this.replaceChildren(html`
            <confirmation-button @confirmation-button:yes="${this._onYes.bind(this)}">
                ${Array.from(this.children)}
            </confirmation-button>
        `);
    }

    _onYes(e) {
        service.resign(this.getAttribute('game-id'))
            .then(() => true)
            .finally(() => e.target.reset());
    }
});
