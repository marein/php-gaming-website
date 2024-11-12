import {html} from 'uhtml/node.js'

customElements.define('event-source-status', class extends HTMLElement {
    connectedCallback() {
        this.replaceChildren(this._statusIndicator = html`
            <span class="status-indicator status-secondary status-indicator-animated"
                  data-title="${this.getAttribute('title-closed')}">
                <span class="status-indicator-circle"></span>
                <span class="status-indicator-circle"></span>
                <span class="status-indicator-circle"></span>
            </span>
        `);
        this._isInErrorState = false;
        this._tooltipTimeout = null;

        document.addEventListener('sse:open', this._open);
        document.addEventListener('sse:error', this._error);
        window.addEventListener('app:load', this._appLoad);
    }

    disconnectedCallback() {
        document.removeEventListener('sse:connected', this._open);
        document.removeEventListener('sse:disconnected', this._error);
        window.removeEventListener('app:load', this._appLoad);
    }

    _appLoad = () => {
        if (!document.querySelector('event-source')) this._open();
    }

    _open = () => {
        this._statusIndicator.classList.remove('status-secondary', 'status-red', 'status-indicator-animated');
        this._statusIndicator.classList.add('status-green');
        this._statusIndicator.dataset.title = this.getAttribute('title-open');

        if (!this._isInErrorState) return;

        this._isInErrorState = false;
        this._forceTooltip();
    }

    _error = () => {
        this._statusIndicator.classList.remove('status-secondary', 'status-green');
        this._statusIndicator.classList.add('status-red', 'status-indicator-animated');
        this._statusIndicator.dataset.title = this.getAttribute('title-closed');

        if (this._isInErrorState) return;

        this._isInErrorState = true;
        this._forceTooltip();
    }

    _forceTooltip() {
        clearTimeout(this._tooltipTimeout);
        this._statusIndicator.dataset.titleShow = '';
        this._tooltipTimeout = setTimeout(() => delete this._statusIndicator.dataset.titleShow, 3000);
    }
});
