customElements.define('event-source', class extends HTMLElement {
    connectedCallback() {
        this._eventSource = null;
        this._lastEventId = null;
        this._subscriptions = this.getAttribute('subscriptions').split(',');
        this._verbose = this.hasAttribute('verbose');
        this._reconnectTimeout = null;

        window.addEventListener('sse:addsubscription', this._onAddSubscription);
        window.addEventListener('app:load', this._onAppLoad);
    }

    disconnectedCallback() {
        window.removeEventListener('sse:addsubscription', this._onAddSubscription);
        window.removeEventListener('app:load', this._onAppLoad);

        this._eventSource && this._eventSource.close();
        clearTimeout(this._reconnectTimeout);
    }

    _connect = (shouldShowSuccess = false) => {
        this._eventSource && this._eventSource.close();
        clearTimeout(this._reconnectTimeout);

        let url = '/sse/sub?id=' + this._subscriptions.join(',');
        if (this._lastEventId !== null) url += '&last_event_id=' + this._lastEventId;

        this._eventSource = new EventSource(url);
        this._eventSource.onmessage = this._onMessage;
        this._eventSource.onopen = () => shouldShowSuccess && window.app.notifyUser('Connected to server.', 'success');
        this._eventSource.onerror = this._onError;
    }

    _onAppLoad = () => {
        this._connect();
    }

    _onMessage = (message) => {
        this._lastEventId = message.lastEventId;

        let [, eventName, eventData] = message.data.split(/([^:]+):(.*)/);
        let payload = JSON.parse(eventData);

        this.dispatchEvent(new CustomEvent(eventName, {bubbles: true, detail: payload}));

        if (this._verbose) console.log(eventName, payload);
    }

    _onError = async () => {
        if (this._eventSource.readyState !== EventSource.CLOSED) return;

        const timeout = 4000 + Math.floor(Math.random() * 2000);
        window.app.notifyUser('No connection to server.', 'danger', timeout);
        this._reconnectTimeout = setTimeout(() => this._connect(true), timeout + 1000);
    }

    _onAddSubscription = (event) => {
        if (this._subscriptions.indexOf(event.detail.name) !== -1) return;

        this._subscriptions.push(event.detail.name);
        this._connect();
    }
});
