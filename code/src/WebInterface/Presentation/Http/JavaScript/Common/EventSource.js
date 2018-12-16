customElements.define('event-source', class extends HTMLElement
{
    connectedCallback()
    {
        let eventSource = new EventSource(
            this.getAttribute('url')
        );

        eventSource.onmessage = this._onMessage.bind(this);
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

        // Dispatch again to satisfy the old js design.
        // todo: Remove this as soon as https://github.com/marein/php-gaming-website/issues/18 is done.
        this.dispatchEvent(
            new CustomEvent(
                'event-for-deprecated-publisher',
                {
                    bubbles: true,
                    detail: {
                        name: eventName,
                        payload: payload
                    },
                }
            )
        );
    }
});
