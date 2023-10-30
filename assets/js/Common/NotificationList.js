customElements.define('notification-list', class extends HTMLElement {
    connectedCallback() {
        this._ul = document.createElement('ul');
        this._ul.classList.add('notification');

        this.append(this._ul);
    }

    /**
     * @param {String} message
     */
    appendMessage(message) {
        let messageNode = this._createMessageNode(message);

        this._ul.insertBefore(messageNode, this._ul.childNodes[0]);

        // Show the message for 3 seconds, fade it out and remove the node.
        setTimeout(() => {
            messageNode.classList.add('notification__message--close');
            setTimeout(() => {
                this._ul.removeChild(messageNode);
            }, 1000);
        }, 3000);
    }

    /**
     * @param {String} message
     * @returns {Node}
     */
    _createMessageNode(message) {
        let smiley = document.createTextNode('¯\\_(ツ)_/¯');
        let br = document.createElement('br');
        let text = document.createTextNode(message);

        let li = document.createElement('li');
        li.classList.add('notification__message');

        li.append(smiley);
        li.append(br);
        li.append(text);

        return li;
    }
});
