import {service} from './ChatService.js'

customElements.define('chat-widget', class extends HTMLElement {
    connectedCallback() {
        this._onDisconnect = [];

        this.innerHTML = `
        <div class="card gp-loading" style="height: 400px" id="chat">
            <div class="card-body scrollable">
                <div class="chat">
                    <div class="chat-bubbles">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <input type="text"
                       name="message"
                       class="form-control"
                       autocomplete="off"
                       placeholder="Type message">
            </div>
        </div>
        `;

        this._chatId = '';
        this._authorId = this.getAttribute('author-id');
        this._input = this.querySelector('[name="message"]');
        this._messageHolder = this.querySelector('.chat-bubbles');
        this._scrollElement = this.querySelector('.card-body');
        this._messageBuffer = [];
        this._isAlreadyInitialized = false;
        this._secondsBeforeRetryAfterLoadMessageFailure = parseInt(this.getAttribute('seconds-before-retry'));

        const chatId = this.getAttribute('chat-id');
        if (chatId) {
            this._initialize(chatId);
        }

        this._registerEventHandler();
    }

    disconnectedCallback() {
        this._onDisconnect.forEach(f => f());
    }

    /**
     * @param {String} chatId
     */
    _initialize(chatId) {
        if (this._chatId === '') {
            this._chatId = chatId;

            this._loadMessages(chatId);
        }
    }

    /**
     * @param {String} chatId
     */
    _loadMessages(chatId) {
        service.messages(chatId)
            .then((messages) => {
                messages.messages.forEach(message => this._appendMessage(message));

                this._isAlreadyInitialized = true;

                this._flushMessageBuffer();

                this.querySelector('.card').classList.remove('gp-loading');
            })
            .catch((e) => {
                // Automatic retry after x seconds.
                setTimeout(() => {
                    this._loadMessages(chatId);
                }, this._secondsBeforeRetryAfterLoadMessageFailure * 1000);
            });
    }

    /**
     * @param {Object} message
     */
    _appendMessage(message) {
        if (!this._isDuplicate(message)) {
            this._messageHolder.append(
                this._createMessageNode(message)
            );

            this._scrollElement.scrollTop = this._scrollElement.scrollHeight;
        }
    }

    /**
     * @param {Object} message
     * @returns {Boolean}
     */
    _isDuplicate(message) {
        return this._messageHolder.querySelector(['[data-id="' + message.messageId + '"]']) !== null;
    }

    _flushMessageBuffer() {
        this._messageBuffer.forEach((message) => {
            this._appendMessage(message);
        });

        this._messageBuffer = [];
    }

    /**
     * @param {Object} message
     * @returns {Node}
     */
    _createMessageNode(message) {
        let writtenAt = new Date(message.writtenAt);
        let hours = ('0' + writtenAt.getHours()).slice(-2);
        let minutes = ('0' + writtenAt.getMinutes()).slice(-2);
        let isSameAuthor = this._authorId === message.authorId;

        let node = document.createElement('div');
        node.classList.add('chat-item');
        node.dataset.id = message.messageId;
        node.innerHTML = `
        <div class="row${isSameAuthor ? ' align-items-end justify-content-end' : ''}">
            <div class="col-11">
                <div class="chat-bubble${isSameAuthor ? ' chat-bubble-me' : ''}">
                    <div class="chat-bubble-title">
                        <div class="row">
                            <div class="col chat-bubble-author"></div>
                            <div class="col-auto chat-bubble-date"></div>
                        </div>
                    </div>
                    <div class="chat-bubble-body">
                        <p></p>
                    </div>
                </div>
            </div>
        </div>`;
        node.querySelector('.chat-bubble-author').append(document.createTextNode('Anonymous'));
        node.querySelector('.chat-bubble-date').append(document.createTextNode(hours + ':' + minutes));
        node.querySelector('p').append(document.createTextNode(message.message));

        return node;
    }

    _clearInput() {
        this._input.value = '';
    }

    _onKeyPress(event) {
        if (event.which !== 13) return;

        event.preventDefault();
        let message = this._input.value;

        this._clearInput();

        if (message.trim() !== '') {
            service.writeMessage(this._chatId, message);
        }
    }

    _onMessageWritten(event) {
        let message = event.detail;

        if (!this._isAlreadyInitialized) {
            this._messageBuffer.push(message);
        } else {
            this._appendMessage(message);
        }
    }

    _onChatAssigned(event) {
        window.dispatchEvent(new CustomEvent('sse:addsubscription', {detail: {name: 'chat-' + event.detail.chatId}}));

        this._initialize(event.detail.chatId);
    }

    _registerEventHandler() {
        this._input.addEventListener('keypress', this._onKeyPress.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('Chat.MessageWritten', this._onMessageWritten.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.ChatAssigned', this._onChatAssigned.bind(this));
    }
});
