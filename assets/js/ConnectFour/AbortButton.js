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

        this._playerId = this.getAttribute('player-id');
        this._players = JSON.parse(this.getAttribute('players'));
        this._moves = new Map(JSON.parse(this.getAttribute('moves')).map(m => [`${m.x},${m.y}`, m]));

        this._changeVisibility();

        window.addEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.addEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.addEventListener('ConnectFour.PlayerMovedFailed', this._onPlayerMovedFailed);
        window.addEventListener('ConnectFour.GameAborted', this._remove);
        window.addEventListener('ConnectFour.GameWon', this._remove);
        window.addEventListener('ConnectFour.GameResigned', this._remove);
        window.addEventListener('ConnectFour.GameDrawn', this._remove);
    }

    disconnectedCallback() {
        window.removeEventListener('ConnectFour.PlayerJoined', this._onPlayerJoined);
        window.removeEventListener('ConnectFour.PlayerMoved', this._onPlayerMoved);
        window.removeEventListener('ConnectFour.PlayerMovedFailed', this._onPlayerMovedFailed);
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
        this._players = [e.detail.redPlayerId, e.detail.yellowPlayerId];

        this._changeVisibility();
    }

    _onPlayerMoved = e => {
        this._moves.set(`${e.detail.x},${e.detail.y}`, e.detail);

        this._changeVisibility();
    }

    _onPlayerMovedFailed = e => {
        this._moves.delete(`${e.detail.x},${e.detail.y}`);

        this._changeVisibility();
    }

    _changeVisibility = () => {
        const abortable = this._moves.size < 2;
        const isPlayer = this._players.indexOf(this._playerId) !== -1;

        this.classList.toggle('d-none', !abortable || !isPlayer);
    }

    _remove = () => {
        this.remove();
    }
});
