import { client } from '../js/Common/HttpClient.js';

// window.app acts like a container.
window.app = {
    baseUrl: '',
    user: {
        id: ''
    }
};

window.app.eventPublisher = new Gaming.Common.EventPublisher();

window.app.notification = new Gaming.Common.Notification(
    document.getElementById('notification')
);

client.onError = (response) => {
    window.app.notification.appendMessage(response.message);
};

window.app.gameService = new Gaming.ConnectFour.GameService(
    client
);

window.app.chatService = new Gaming.Chat.ChatService(
    client
);

// Forward events to app.eventPublisher which is used by the old js design.
// todo: Remove this as soon as https://github.com/marein/php-gaming-website/issues/18 is done.
window.addEventListener(
    'event-for-deprecated-publisher',
    function (event) {
        window.app.eventPublisher.publish(event.detail);
    }
);
