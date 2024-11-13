import {html} from 'uhtml/node.js'

customElements.define('confirmation-button', class extends HTMLElement {
    connectedCallback() {
        this._initialChildren = Array.from(this.children);
        this._button = this.querySelector('button');
        this._onDocumentClick = e => !this.contains(e.target) && this.reset();

        this._button?.addEventListener('click', this._onButtonClick.bind(this));
    }

    disconnectedCallback() {
        this.reset();
    }

    reset() {
        document.removeEventListener('click', this._onDocumentClick);
        this.replaceChildren(...this._initialChildren);
        this._button?.removeAttribute('disabled');
        this._button?.classList.remove('btn-loading');
    }

    _onButtonClick(e) {
        setTimeout(() => document.addEventListener('click', this._onDocumentClick), 0);

        this.replaceChildren(html`
            <div class="row row-gap-2">
                <div class="col-6">
                    <button @click="${this.reset.bind(this)}"
                            class="btn btn-outline-danger btn-icon w-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="icon">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M18 6l-12 12"/>
                            <path d="M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="col-6">
                    <button @click="${this._onYesClick.bind(this)}"
                            class="btn btn-outline-success btn-icon w-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             class="icon">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M5 12l5 5l10 -10"/>
                        </svg>
                    </button>
                </div>
            </div>
        `);
    }

    _onYesClick(e) {
        document.removeEventListener('click', this._onDocumentClick);
        this.replaceChildren(...this._initialChildren);
        this._button?.setAttribute('disabled', '');
        this._button?.classList.add('btn-loading');

        this.dispatchEvent(new CustomEvent('confirmation-button:yes'));
    }
});
