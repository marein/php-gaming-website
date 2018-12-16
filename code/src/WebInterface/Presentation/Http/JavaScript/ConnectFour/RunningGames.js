customElements.define('running-games', class extends HTMLElement
{
    connectedCallback()
    {
        this._registerEventHandler();
    }

    _onRunningGamesUpdated(event)
    {
        this.innerText = event.detail.count;
    }

    _registerEventHandler()
    {
        window.addEventListener(
            'ConnectFour.RunningGamesUpdated',
            this._onRunningGamesUpdated.bind(this)
        );
    }
});
