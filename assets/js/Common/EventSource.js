/**
 * @typedef {{[key: number]: Subscription}} Subscriptions
 * @typedef {{channel: string, listeners: Listeners}} Subscription
 * @typedef {{[key: string]: Function}} Listeners
 * @typedef {{type: string, channels: string[], payload: string}} Message
 */

const eventTarget = new EventTarget();
const globalConfig = document.querySelector('meta[name="sse-config"]');
let currentSubscriptionId = 0;
const subscriptions = {};
let eventSource = null;
const baseUrl = globalConfig?.getAttribute('data-base-url') || '/sse/sub?id=';
let debounceTimeout = null;
const globalDebounceTimeoutMs = parseInt(globalConfig?.getAttribute('data-debounce-ms') ?? 150);
let activeChannels = [];
let messageBuffer = [];
const messageBufferSize = parseInt(globalConfig?.getAttribute('data-message-buffer-size') ?? 100);
let messageTimeoutMs = parseInt(globalConfig?.getAttribute('data-message-timeout-ms') ?? 10000);

setInterval(() => {
    const now = Date.now();
    messageBuffer = messageBuffer.filter(m => m.removeAfter > now);
}, 1000);

function connect(debounceTimeoutMs = null) {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        if (eventSource) eventSource.close();

        activeChannels = [...new Set(Object.values(subscriptions).map(s => s.channel))];
        if (activeChannels.length === 0) return;

        eventSource = new EventSource(baseUrl + activeChannels.join(','));
        eventSource.onmessage = onMessage;
        eventSource.onopen = onOpen;
        eventSource.onerror = onError;
    }, debounceTimeoutMs ?? globalDebounceTimeoutMs);
}

function onMessage(event) {
    let [, type, channels, payload] = event.data.match(/([^:]+):([^:]+):(.*)/);
    channels = channels.split(',').filter(c => activeChannels.indexOf(c) !== -1);

    const message = {type, channels, payload, removeAfter: Date.now() + messageTimeoutMs};
    messageBuffer.push(message);
    if (messageBuffer.length > messageBufferSize) {
        messageBuffer.shift();
    }

    publish(subscriptions, [message]);
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
 * @param {Subscriptions} subscriptions
 * @param {Message[]} messages
 */
function publish(subscriptions, messages) {
    Object.values(subscriptions).forEach(s => {
        messages.forEach(m => {
            if (m.channels.indexOf(s.channel) === -1) return;
            s.listeners[m.type]?.({type: m.type, detail: JSON.parse(m.payload)})
        });
    });
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
    doesChannelExist && publish([subscriptions[subscriptionId]], messageBuffer);
    !doesChannelExist && connect();
}

export const addEventListener = (...args) => eventTarget.addEventListener(...args);
export const removeEventListener = (...args) => eventTarget.removeEventListener(...args);
