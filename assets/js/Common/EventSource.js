class EventSourceElement extends HTMLElement
{
    connectedCallback()
    {
        this._eventSource = null;
        this._lastEventId = null;
        this._subscriptions = this.getAttribute('subscriptions').split(',');
        this._verbose = this.hasAttribute('verbose');
        this._onDisconnect = [() => this._eventSource && this._eventSource.close()];

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('sse:addsubscription', this._onAddSubscription.bind(this));

        this._connect();
    }

    disconnectedCallback()
    {
        this._onDisconnect.forEach(f => f());
    }

    _connect()
    {
        if (this._eventSource) this._eventSource.close();

        let url = '/sse/sub?id=' + this._subscriptions.join(',');
        if (this._lastEventId !== null) url += '&last_event_id=' + this._lastEventId;

        this._eventSource = new EventSource(url);
        this._eventSource.onmessage = this._onMessage.bind(this);
    }

    _onMessage(message)
    {
        this._lastEventId = message.lastEventId;

        let payload = JSON.parse(message.data);
        let eventName = payload.eventName;
        delete payload.eventName;

        this.dispatchEvent(new CustomEvent(eventName, {bubbles: true, detail: payload}));

        if (this._verbose) console.log(eventName, payload);
    }

    _onAddSubscription(event)
    {
        if (this._subscriptions.indexOf(event.detail.name) !== -1) return;

        this._subscriptions.push(event.detail.name);
        this._connect();
    }
}

customElements.define('event-source', EventSourceElement);
