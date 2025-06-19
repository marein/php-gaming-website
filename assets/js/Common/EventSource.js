/**
 * @typedef {{[key: string]: Function}} Listeners
 */

const eventTarget = new EventTarget();
const globalConfig = document.querySelector('meta[name="sse-config"]');
let currentSubscriptionId = 0;
const subscriptions = {};
let eventSource = null;
const baseUrl = globalConfig?.getAttribute('data-base-url') || '/sse/sub?id=';
let debounceTimeout = null;
const globalDebounceTimeoutMs = globalConfig?.getAttribute('data-debounce-ms') ?? 150;

function connect(debounceTimeoutMs = null) {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        if (eventSource) eventSource.close();

        const uniqueChannels = [...new Set(Object.values(subscriptions).map(s => s.channel))];
        if (uniqueChannels.length === 0) return;

        eventSource = new EventSource(baseUrl + uniqueChannels.join(','));
        eventSource.onmessage = onMessage;
        eventSource.onopen = onOpen;
        eventSource.onerror = onError;
    }, debounceTimeoutMs ?? globalDebounceTimeoutMs);
}

function onMessage(event) {
    let [, type, channels, payload] = event.data.match(/([^:]+):([^:]+):(.*)/);
    channels = channels.split(',');

    Object.values(subscriptions).forEach(s => {
        if (channels.indexOf(s.channel) === -1) return;
        s.listeners[type]?.({type, detail: JSON.parse(payload)})
    });
}

function onOpen() {
    eventTarget.dispatchEvent(new CustomEvent('open'))
}

function onError () {
    eventTarget.dispatchEvent(new CustomEvent('error'));

    if (eventSource.readyState !== EventSource.CLOSED) return;

    connect(3000 + Math.floor(Math.random() * 2000));
}

/**
 * @param {String} channel
 * @param {Listeners} listeners
 * @param {AbortSignal|null} signal
 */
export function subscribe(channel, listeners, signal = null) {
    const doesChannelExist = Object.values(subscriptions).some(s => s.channel === channel);
    const subscriptionId = ++currentSubscriptionId;
    subscriptions[subscriptionId] = {channel, listeners};
    signal?.addEventListener('abort', () => {
        delete subscriptions[subscriptionId];
        const doesChannelExist = Object.values(subscriptions).some(s => s.channel === channel);
        !doesChannelExist && connect();
    });
    !doesChannelExist && connect();
}

export const addEventListener = (...args) => eventTarget.addEventListener(...args);
export const removeEventListener = (...args) => eventTarget.removeEventListener(...args);
