class EventSourceElement extends HTMLElement
{
    constructor(props)
    {
        super(props);

        this._subscriptions = [];
        this._verbose = false;
        this._eventSource = null;
        this._lastEventId = null;

        this._registerEventHandler();
    }

    connectedCallback()
    {
        this._subscriptions = this.getAttribute('subscriptions').split(',');
        this._verbose = this.hasAttribute('verbose');

        this._connectEventSource();
    }

    _connectEventSource()
    {
        if (this._eventSource) {
            this._eventSource.close();
        }

        let url = '/sse/sub?id=' + this._subscriptions.join(',');
        if (this._lastEventId !== null) {
            url += '&last_event_id=' + this._lastEventId;
        }

        this._eventSource = new EventSource(url);
        this._eventSource.onmessage = this._onMessage.bind(this);
    }

    _onMessage(message)
    {
        this._lastEventId = message.lastEventId;

        let payload = JSON.parse(message.data);
        let eventName = payload.eventName;
        delete payload.eventName;

        this.dispatchEvent(
            new CustomEvent(
                eventName,
                {
                    bubbles: true,
                    detail: payload
                }
            )
        );

        this._logOnVerbose(eventName, payload);
    }

    _onAddSubscription(event)
    {
        if (this._hasSubscription(event.detail.name)) {
            return;
        }

        this._subscriptions.push(event.detail.name);
        this._connectEventSource();
    }

    _logOnVerbose(eventName, payload)
    {
        if (this._verbose) {
            console.log(eventName, payload);
        }
    }

    _hasSubscription(name)
    {
        return this._subscriptions.indexOf(event.detail.name) !== -1;
    }

    _registerEventHandler()
    {
        window.addEventListener(
            'AddSubscription',
            this._onAddSubscription.bind(this)
        );
    }
}

customElements.define('event-source', EventSourceElement);
