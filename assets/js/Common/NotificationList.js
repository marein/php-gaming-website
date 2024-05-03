customElements.define('notification-list', class extends HTMLElement {
    /**
     * @param {String} message
     */
    async appendMessage(message) {
        let messageNode = this._createMessageNode(message);

        this.insertBefore(messageNode, this.childNodes[0]);

        await new Promise(resolve => setTimeout(resolve, 3000));
        messageNode.classList.add('gp-fadeout');

        await new Promise(resolve => setTimeout(resolve, 1000));
        this.removeChild(messageNode);
    }

    /**
     * @param {String} message
     * @returns {Node}
     */
    _createMessageNode(message) {
        let node = document.createElement('div');
        node.classList.add('alert', 'alert-important', 'alert-warning');
        node.innerHTML = `
        <div class="d-flex">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                 stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M12 9v4"></path>
                <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path>
                <path d="M12 16h.01"></path>
            </svg>
            <div></div>
        </div>`;

        node.querySelector('div div:nth-child(2)').append(document.createTextNode(message));

        return node;
    }
});
