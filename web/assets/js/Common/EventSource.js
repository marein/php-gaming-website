class EventSourceElement extends HTMLElement
{
    constructor(props)
    {
        super(props);

        this._verbose = false;
    }

    connectedCallback()
    {
        let eventSource = new EventSource(
            this.getAttribute('url')
        );
        eventSource.onmessage = this._onMessage.bind(this);

        this._verbose = this.hasAttribute('verbose');
    }

    _onMessage(message)
    {
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

    _logOnVerbose(eventName, payload)
    {
        if (this._verbose) {
            console.log(eventName, payload);
        }
    }
}

customElements.define('event-source', EventSourceElement);
