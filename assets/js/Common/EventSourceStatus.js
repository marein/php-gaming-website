customElements.define('event-source-status', class extends HTMLElement {
    connectedCallback() {
        this.innerHTML = `
            <span class="status-indicator status-secondary status-indicator-animated">
                <span class="status-indicator-circle"></span>
                <span class="status-indicator-circle"></span>
                <span class="status-indicator-circle"></span>
            </span>
        `;

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
        this.querySelector('.status-indicator').classList.remove('status-secondary', 'status-red', 'status-indicator-animated');
        this.querySelector('.status-indicator').classList.add('status-green');
    }

    _error = () => {
        this.querySelector('.status-indicator').classList.remove('status-secondary', 'status-green');
        this.querySelector('.status-indicator').classList.add('status-red', 'status-indicator-animated');
    }
});
