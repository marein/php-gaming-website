// window.app acts like a container.
window.app = {
    baseUrl: '',
    user: {
        id: ''
    }
};

app.eventPublisher = new Gaming.Common.EventPublisher();

app.notification = new Gaming.Common.Notification(
    document.getElementById('notification')
);

app.httpClient = new Gaming.Common.HttpClient(
    app.baseUrl,
    app.notification
);

app.gameService = new Gaming.ConnectFour.GameService(
    app.httpClient
);

app.chatService = new Gaming.Chat.ChatService(
    app.httpClient
);

// Forward events to app.eventPublisher which is used by the old js design.
// todo: Remove this as soon as https://github.com/marein/php-gaming-website/issues/18 is done.
window.addEventListener(
    'event-for-deprecated-publisher',
    function (event) {
        app.eventPublisher.publish(event.detail);
    }
);
