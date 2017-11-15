// window.app acts like a container.
window.app = {
    isDebug: false,
    user: {
        id: ''
    }
};

// Wrapper around console.log. Log only if debug mode is enabled.
app.log = function (message) {
    if (app.isDebug) {
        console.log(message);
    }
};

app.eventPublisher = new Gambling.Common.EventPublisher();

app.gameService = new Gambling.ConnectFour.GameService();

app.chatService = new Gambling.Chat.ChatService();

app.notification = new Gambling.Common.Notification(
    document.getElementById('notification')
);

// Log all events if debug mode is enabled.
app.eventPublisher.subscribe({
    isSubscribedTo: () => {
        return true;
    },
    handle: (event) => {
        app.log(event);
    }
});

/**
 * Register startEventSource function to global namespace and ensure,
 * that only one is started. This EventSource publishes all messages to
 * the eventPublisher.
 */
(function () {
    let eventSource = null;

    window.startEventSource = function (url) {
        if (eventSource !== null) {
            throw 'An EventSource is already started.';
        }

        eventSource = new EventSource(url);

        // Redirect all messages to eventPublisher.
        eventSource.onmessage = function (message) {
            let payload = JSON.parse(message.data);
            let eventName = payload.eventName;
            delete payload.eventName;

            app.eventPublisher.publish({
                name: eventName,
                payload: payload
            });
        };
    };
})();
