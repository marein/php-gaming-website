import {service} from './GameService.js'
import 'confirmation-button'
import {html} from 'uhtml/node.js'

customElements.define('connect-four-abort-button', class extends HTMLElement {
    connectedCallback() {
        this.replaceChildren(html`
            <confirmation-button @confirmation-button:yes="${this._onYes.bind(this)}">
                ${Array.from(this.children)}
            </confirmation-button>
        `);
    }

    _onYes(e) {
        service.abort(this.getAttribute('game-id'))
            .then(() => true)
            .finally(() => e.target.reset());
    }
});
