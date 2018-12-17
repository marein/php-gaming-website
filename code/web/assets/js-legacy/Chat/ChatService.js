var Gaming = Gaming || {};
Gaming.Chat = Gaming.Chat || {};

Gaming.Chat.ChatService = class
{
    /**
     * @param {HttpClient} httpClient
     */
    constructor(httpClient)
    {
        this.httpClient = httpClient;
    }

    /**
     * @param {String} chatId
     * @param {String} message
     * @returns {Promise}
     */
    writeMessage(chatId, message)
    {
        return this.httpClient.post(
            '/api/chat/chats/' + chatId + '/write-message',
            {
                message: message
            }
        );
    }

    /**
     * @param {String} chatId
     * @returns {Promise}
     */
    messages(chatId)
    {
        return this.httpClient.get(
            '/api/chat/chats/' + chatId + '/messages'
        );
    }
};
