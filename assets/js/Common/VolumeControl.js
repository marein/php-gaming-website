import {html} from 'uhtml/node.js'
import * as scriptune from 'https://cdn.jsdelivr.net/gh/marein/js-scriptune@main/src/scriptune.js'

customElements.define('volume-control', class extends HTMLElement {
    connectedCallback() {
        this.append(html`
            <input type="checkbox" class="gp-dropdown-toggle" id="volume-control-dropdown">
            <label class="nav-link" for="volume-control-dropdown">
                ${this._iconHighVolume = html`
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M15 8a5 5 0 0 1 0 8"/>
                        <path d="M17.7 5a9 9 0 0 1 0 14"/>
                        <path d="M6 15h-2a1 1 0 0 1 -1 -1v-4a1 1 0 0 1 1 -1h2l3.5 -4.5a.8 .8 0 0 1 1.5 .5v14a.8 .8 0 0 1 -1.5 .5l-3.5 -4.5"/>
                    </svg>`}
                ${this._iconLowVolume = html`
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M15 8a5 5 0 0 1 0 8"/>
                        <path d="M6 15h-2a1 1 0 0 1 -1 -1v-4a1 1 0 0 1 1 -1h2l3.5 -4.5a.8 .8 0 0 1 1.5 .5v14a.8 .8 0 0 1 -1.5 .5l-3.5 -4.5"/>
                    </svg>
                `}
                ${this._iconMute = html`
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M6 15h-2a1 1 0 0 1 -1 -1v-4a1 1 0 0 1 1 -1h2l3.5 -4.5a.8 .8 0 0 1 1.5 .5v14a.8 .8 0 0 1 -1.5 .5l-3.5 -4.5"/>
                        <path d="M16 10l4 4m0 -4l-4 4"/>
                    </svg>
                `}
            </label>
            <div class="dropdown-menu dropdown-menu-card dropdown-menu-arrow dropdown-menu-end"
                 data-bs-popper>
                <div class="card">
                    <div class="card-body">
                        ${this._control = html`
                            <input type="range" class="form-range" value="1" min="0" max="1" step=".1"/>
                        `}
                        ${this._output = html`
                            <span class="badge bg-primary-lt w-100"></span>
                        `}
                    </div>
                </div>
            </div>
        `);

        this._storageKey = this.getAttribute('storage-key') || 'volume-control';
        this._volume = this._volume;
        this._abortController = new AbortController();

        this._control.addEventListener('input', this._onVolumeChange.bind(this));
    }

    /**
     * @param {Number} value
     */
    set _volume(value) {
        localStorage.setItem(this._storageKey, value);
        this._control.value = value;
        this._updateElements();
        scriptune.setMasterVolume(value);
    }

    get _volume() {
        return Math.max(0, Math.min(parseFloat(localStorage.getItem(this._storageKey) || 1), 1));
    }

    _updateElements() {
        this._output.innerText = `${this._volume * 100}%`;
        this._iconHighVolume.classList.toggle('d-none', this._volume <= 0.6);
        this._iconLowVolume.classList.toggle('d-none', this._volume === 0 || this._volume > 0.6);
        this._iconMute.classList.toggle('d-none', this._volume !== 0);
    }

    _onVolumeChange(event) {
        this._volume = parseFloat(event.target.value);

        this._abortController.abort();
        this._abortController = new AbortController();
        scriptune.play('C4:s', {signal: this._abortController.signal});
    }
});
