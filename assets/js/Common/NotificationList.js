import {html} from 'uhtml/node.js'

customElements.define('notification-list', class extends HTMLElement {
    /**
     * @param {String} message
     * @param {String} type
     * @param {Number} timeout
     */
    async appendMessage(message, type, timeout = 3000) {
        let messageNode = html`
            <div class="${`alert alert-important alert-dismissible alert-${type}`}"
                 style="${`--tblr-alert-bg: var(--tblr-${type}-lt);`}">
                <div class="alert-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                         class="icon">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M12 9v4"></path>
                        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"></path>
                        <path d="M12 16h.01"></path>
                    </svg>
                </div>
                ${message}
                <button class="btn-close" @click="${e => this._scheduleRemoval(e.target.parentElement, 0)}"></button>
            </div>
        `;

        this.insertBefore(messageNode, this.childNodes[0]);

        let handle = this._scheduleRemoval(messageNode, timeout);

        messageNode.addEventListener('mouseenter', () => clearTimeout(handle));
        messageNode.addEventListener('mouseleave', () => handle = this._scheduleRemoval(messageNode, timeout));
    }

    _scheduleRemoval = (messageNode, timeout) => {
        return setTimeout(() => {
            messageNode.addEventListener('animationend', () => this.removeChild(messageNode));
            messageNode.classList.add('gp-fadeout')
        }, timeout);
    }
});
