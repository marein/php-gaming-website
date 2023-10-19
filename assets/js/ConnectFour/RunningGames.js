class RunningGamesElement extends HTMLElement
{
    connectedCallback()
    {
        this._onDisconnect = [];

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.RunningGamesUpdated', this._onRunningGamesUpdated.bind(this));
    }

    disconnectedCallback()
    {
        this._onDisconnect.forEach(f => f());
    }

    _onRunningGamesUpdated(event)
    {
        this.innerText = event.detail.count;
    }
}

customElements.define('running-games', RunningGamesElement);
