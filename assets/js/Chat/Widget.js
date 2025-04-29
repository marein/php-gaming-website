import {service} from './ChatService.js'
import {html} from 'uhtml/node.js'
import * as sse from '../Common/EventSource.js'

customElements.define('chat-widget', class extends HTMLElement {
    connectedCallback() {
        this._sseAbortController = new AbortController();

        this.append(this._rootElement = html`
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
        `);

        this._chatId = '';
        this._authorId = this.getAttribute('author-id');
        this._input = this.querySelector('[name="message"]');
        this._messageHolder = this.querySelector('.chat-bubbles');
        this._scrollElement = this.querySelector('.card-body');
        this._messageBuffer = [];
        this._isAlreadyInitialized = false;
        this._secondsBeforeRetryAfterLoadMessageFailure = parseInt(this.getAttribute('seconds-before-retry'));

        const chatId = this.getAttribute('chat-id');
        if (chatId) this._initialize(chatId);

        window.addEventListener('WebInterface.UserArrived', this._onUserArrived);
        sse.subscribe(this.getAttribute('game-channel'), {
            'ConnectFour.ChatAssigned': this._onChatAssigned.bind(this)
        }, this._sseAbortController.signal);
    }

    disconnectedCallback() {
        window.removeEventListener('WebInterface.UserArrived', this._onUserArrived);
        this._sseAbortController.abort();
    }

    /**
     * @param {String} chatId
     */
    _initialize(chatId) {
        if (this._chatId !== '') return;

        this._chatId = chatId;
        this._loadMessages(chatId);

        this._input.addEventListener('keypress', this._onKeyPress.bind(this));

        sse.subscribe(`chat-${chatId}`, {
            'Chat.MessageWritten': this._onMessageWritten.bind(this)
        }, this._sseAbortController.signal);
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

                this._rootElement.classList.remove('gp-loading');
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
        const writtenAt = new Date(message.writtenAt);
        const hours = ('0' + writtenAt.getHours()).slice(-2);
        const minutes = ('0' + writtenAt.getMinutes()).slice(-2);
        const isSameAuthor = this._authorId === message.authorId;

        return html`
            <div class="${`row${isSameAuthor ? ' align-items-end justify-content-end' : ''}`}"
                 data-id="${message.messageId}" data-author-id="${message.authorId}">
                <div class="col-11">
                    <div class="${`chat-bubble${isSameAuthor ? ' chat-bubble-me' : ''}`}">
                        <div class="chat-bubble-title">
                            <div class="row">
                                <div class="col chat-bubble-author">${'Anonymous'}</div>
                                <div class="col-auto chat-bubble-date">${hours + ':' + minutes}</div>
                            </div>
                        </div>
                        <div class="chat-bubble-body">
                            <p>${message.message}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
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
        this._initialize(event.detail.chatId);
    }

    _onUserArrived = event => {
        this._authorId = event.detail.userId;

        this.querySelectorAll(`[data-author-id="${this._authorId}"]`).forEach(message => {
            message.querySelector('.chat-bubble').classList.add('chat-bubble-me');
            message.querySelector('.row').classList.add('align-items-end', 'justify-content-end');
        });
    }
});
