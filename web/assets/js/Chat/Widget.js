import { service } from './ChatService.js'

class WidgetElement extends HTMLElement
{
    connectedCallback()
    {
        this.classList.add('loading-indicator');

        this._messageHolder = document.createElement('ul');
        this._messageHolder.classList.add('chat__messages');

        this._input = document.createElement('textarea');
        this._input.classList.add('chat__input');
        this._input.setAttribute('type', 'text');
        this._input.setAttribute('name', 'message');

        this.append(this._messageHolder);
        this.append(this._input);

        this._chatId = '';
        this._messageBuffer = [];
        this._isAlreadyInitialized = false;
        this._secondsBeforeRetryAfterLoadMessageFailure = parseInt(this.getAttribute('seconds-before-retry'));

        let chatId = this.getAttribute('chat-id');

        if (chatId) {
            this._initialize(chatId);
        }

        this._registerEventHandler();
    }

    /**
     * @param {String} chatId
     */
    _initialize(chatId)
    {
        if (this._chatId === '') {
            this._chatId = chatId;

            this._loadMessages(chatId);
        }
    }

    /**
     * @param {String} chatId
     */
    _loadMessages(chatId)
    {
        service.messages(chatId).then((messages) => {
            messages.forEach((message) => {
                this._appendMessage(message);
            });

            this._isAlreadyInitialized = true;

            this._flushMessageBuffer();

            this.classList.remove('loading-indicator');
        }).catch(() => {
            // Automatic retry after x seconds.
            setTimeout(() => {
                this._loadMessages(chatId);
            }, this._secondsBeforeRetryAfterLoadMessageFailure * 1000);
        });
    }

    /**
     * @param {Object} message
     */
    _appendMessage(message)
    {
        if (!this._isDuplicate(message)) {
            this._messageHolder.append(
                this._createMessageNode(message)
            );

            this._messageHolder.scrollTop = this._messageHolder.scrollHeight;
        }
    }

    /**
     * @param {Object} message
     * @returns {Boolean}
     */
    _isDuplicate(message)
    {
        return this._messageHolder.querySelector(['[data-id="' + message.messageId + '"]']) !== null;
    }

    _flushMessageBuffer()
    {
        this._messageBuffer.forEach((message) => {
            this._appendMessage(message);
        });

        this._messageBuffer = [];
    }

    /**
     * @param {Object} message
     * @returns {Node}
     */
    _createMessageNode(message)
    {
        let writtenAt = new Date(message.writtenAt);
        let hours = ('0' + writtenAt.getHours()).slice(-2);
        let minutes = ('0' + writtenAt.getMinutes()).slice(-2);

        let author = 'Anonymous';

        let span = document.createElement('span');
        span.innerText = hours + ':' + minutes + ' - ' + author;

        let text = document.createTextNode(message.message);

        let li = document.createElement('li');
        li.dataset.id = message.messageId;
        li.classList.add('chat__messages__message');
        li.append(span);
        li.append(text);

        return li;
    }

    _clearInput()
    {
        this._input.value = '';
    }

    _onKeyPress(event)
    {
        if (event.which === 13 && !event.shiftKey) {
            event.preventDefault();
            let message = this._input.value;

            this._clearInput();

            if (message.trim() !== '') {
                service.writeMessage(
                    this._chatId,
                    message
                );
            }
        }
    }

    _onMessageWritten(event)
    {
        let message = event.detail;

        if (!this._isAlreadyInitialized) {
            this._messageBuffer.push(message);
        } else {
            this._appendMessage(message);
        }
    }

    _onChatAssigned(event)
    {
        this._initialize(event.detail.chatId);
    }

    _registerEventHandler()
    {
        this._input.addEventListener('keypress', this._onKeyPress.bind(this));

        window.addEventListener(
            'Chat.MessageWritten',
            this._onMessageWritten.bind(this)
        );

        window.addEventListener(
            'ConnectFour.ChatAssigned',
            this._onChatAssigned.bind(this)
        );
    }
}

customElements.define('chat-widget', WidgetElement);
