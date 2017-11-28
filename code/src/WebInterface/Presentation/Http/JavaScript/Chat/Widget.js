var Gambling = Gambling || {};
Gambling.Chat = Gambling.Chat || {};

Gambling.Chat.Widget = class
{
    /**
     * @param {Gambling.Common.EventPublisher} eventPublisher
     * @param {Gambling.Chat.ChatService} chatService
     * @param {Node} element
     * @param {String} chatId
     */
    constructor(eventPublisher, chatService, element, chatId)
    {
        this.eventPublisher = eventPublisher;
        this.chatService = chatService;
        this.element = element;
        this.messageHolder = element.querySelector('[data-message-holder]');
        this.input = element.querySelector('[data-input]');
        this.chatId = '';
        this.messageBuffer = [];
        this.isAlreadyInitialized = false;
        this.secondsBeforeRetryAfterLoadMessageFailure = 3;

        this.element.classList.add('loading-indicator');

        if (chatId) {
            this.initialize(chatId);
        }

        this.registerEventHandler();
    }

    /**
     * @param {String} chatId
     */
    initialize(chatId)
    {
        if (this.chatId === '') {
            this.chatId = chatId;

            this.loadMessages(chatId);
        }
    }

    /**
     * @param {String} chatId
     */
    loadMessages(chatId)
    {
        this.chatService.messages(chatId).then((messages) => {
            messages.forEach((message) => {
                this.appendMessage(message);
            });

            this.isAlreadyInitialized = true;

            this.flushMessageBuffer();

            this.element.classList.remove('loading-indicator');
        }).catch(() => {
            // Automatic retry after x seconds.
            setTimeout(() => {
                this.loadMessages(chatId);
            }, this.secondsBeforeRetryAfterLoadMessageFailure * 1000);
        });
    }

    /**
     * @param {Object} message
     */
    appendMessage(message)
    {
        if (!this.isDuplicate(message)) {
            this.messageHolder.append(
                this.createMessageNode(message)
            );

            this.messageHolder.scrollTop = this.messageHolder.scrollHeight;
        }
    }

    /**
     * @param {Object} message
     * @returns {Boolean}
     */
    isDuplicate(message)
    {
        return this.messageHolder.querySelector(['[data-id="' + message.messageId + '"]']) !== null;
    }

    flushMessageBuffer()
    {
        this.messageBuffer.forEach((message) => {
            this.appendMessage(message);
        });

        this.messageBuffer = [];
    }

    /**
     * @param {String} message
     * @returns {Node}
     */
    createMessageNode(message)
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

    clearInput()
    {
        this.input.value = '';
    }

    onKeyPress(event)
    {
        if (event.which === 13 && !event.shiftKey) {
            event.preventDefault();
            let message = this.input.value;

            this.clearInput();

            if (message.trim() !== '') {
                this.chatService.writeMessage(
                    this.chatId,
                    message
                );
            }
        }
    }

    onMessageWritten(event)
    {
        let message = event.payload;

        if (!this.isAlreadyInitialized) {
            this.messageBuffer.push(message);
        } else {
            this.appendMessage(message);
        }
    }

    onChatAssigned(event)
    {
        this.initialize(event.payload.chatId);
    }

    registerEventHandler()
    {
        this.input.addEventListener('keypress', this.onKeyPress.bind(this));

        this.eventPublisher.subscribe({
            isSubscribedTo: (event) => {
                return event.name === 'chat.message-written';
            },
            handle: this.onMessageWritten.bind(this)
        });

        this.eventPublisher.subscribe({
            isSubscribedTo: (event) => {
                return event.name === 'connect-four.chat-assigned';
            },
            handle: this.onChatAssigned.bind(this)
        });
    }
};
