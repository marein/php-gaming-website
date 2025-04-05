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

        this._changeVisibility();

        window.addEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.addEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.addEventListener('ConnectFour.GameAborted', this._remove);
        window.addEventListener('ConnectFour.GameWon', this._remove);
        window.addEventListener('ConnectFour.GameResigned', this._remove);
        window.addEventListener('ConnectFour.GameDrawn', this._remove);
    }

    disconnectedCallback() {
        window.removeEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.removeEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.removeEventListener('ConnectFour.GameWon', this._remove);
        window.removeEventListener('ConnectFour.GameAborted', this._remove);
        window.removeEventListener('ConnectFour.GameResigned', this._remove);
        window.removeEventListener('ConnectFour.GameDrawn', this._remove);
    }

    _onYes(e) {
        service.abort(this.getAttribute('game-id'))
            .then(() => true)
            .finally(() => e.target.reset());
    }

    _onPlayerJoined = e => {
        this.setAttribute('players', JSON.stringify([e.detail.redPlayerId, e.detail.yellowPlayerId]));

        this._changeVisibility();
    }

    _onPlayerMoved = e => {
        const moves = JSON.parse(this.getAttribute('moves'));
        moves.push(e.detail);
        this.setAttribute('moves', JSON.stringify(moves));

        this._changeVisibility();
    }

    _changeVisibility = () => {
        const abortable = new Map(JSON.parse(this.getAttribute('moves')).map(m => [`${m.x},${m.y}`, m])).size < 2;
        const isPlayer = JSON.parse(this.getAttribute('players')).indexOf(this.getAttribute('player-id')) !== -1;

        this.classList.toggle('d-none', !abortable || !isPlayer);
    }

    _remove = () => {
        this.remove();
    }
});
