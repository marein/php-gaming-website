customElements.define('event-source', class extends HTMLElement {
    connectedCallback() {
        this._eventSource = null;
        this._lastEventId = null;
        this._reconnectTimeout = null;
        this._subscriptions = this.getAttribute('subscriptions')?.split(',') ?? [];
        this._verbose = this.hasAttribute('verbose');

        window.addEventListener('sse:addsubscription', this._onAddSubscription);
        window.addEventListener('app:load', this._connect);
    }

    disconnectedCallback() {
        window.removeEventListener('sse:addsubscription', this._onAddSubscription);
        window.removeEventListener('app:load', this._connect);

        this._eventSource && this._eventSource.close();
        clearTimeout(this._reconnectTimeout);
    }

    _connect = () => {
        this._eventSource && this._eventSource.close();
        clearTimeout(this._reconnectTimeout);

        let url = '/sse/sub?id=' + this._subscriptions.join(',');
        if (this._lastEventId !== null) url += '&last_event_id=' + this._lastEventId;

        this._eventSource = new EventSource(url);
        this._eventSource.onmessage = (message) => {
            this._lastEventId = message.lastEventId;

            let [, eventName, eventData] = message.data.split(/([^:]+):(.*)/);
            let payload = JSON.parse(eventData);

            this.dispatchEvent(new CustomEvent(eventName, {bubbles: true, detail: payload}));

            this._verbose && console.log(eventName, payload);
        };
        this._eventSource.onopen = () => this.dispatchEvent(new CustomEvent('sse:open', {bubbles: true}));
        this._eventSource.onerror = () => {
            this.dispatchEvent(new CustomEvent('sse:error', {bubbles: true}));

            if (this._eventSource.readyState !== EventSource.CLOSED) return;

            this._reconnectTimeout = setTimeout(() => this._connect(), 3000 + Math.floor(Math.random() * 2000));
        };
    }

    _onAddSubscription = (event) => {
        if (this._subscriptions.indexOf(event.detail.name) !== -1) return;

        this._subscriptions.push(event.detail.name);
        this._connect();
    }
});
