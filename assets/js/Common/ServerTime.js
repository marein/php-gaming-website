const globalConfig = document.querySelector('meta[name="server-time-config"]');
const url = globalConfig?.getAttribute('data-url') || '/api/server-time';
const defaultMaxJitter = parseInt(globalConfig?.getAttribute('data-max-jitter') || 1000);
const defaultDelay = parseInt(globalConfig?.getAttribute('data-delay') || 100);
let offsetMs = 0;

sync();

/**
 * @param {Number} retries
 * @param {Number} delay
 * @param {Number} maxJitter
 */
export async function sync(retries = 5, delay = defaultDelay, maxJitter = defaultMaxJitter) {
    try {
        const requestTime = Date.now();
        const response = await fetch(url);
        if (!response.ok) throw new Error();
        const serverTime = await response.json();
        const roundTripTime = Date.now() - requestTime;
        const timeWhenServerCreatedServerTime = requestTime + roundTripTime / 2;
        offsetMs = serverTime - timeWhenServerCreatedServerTime;
        window.dispatchEvent(new CustomEvent('server-time:sync', {detail: { offsetMs }}));
    } catch (e) {
        if (retries <= 0) {
            window.dispatchEvent(new CustomEvent('server-time:sync-error'));
            return;
        }

        await new Promise(r => setTimeout(r, delay + Math.floor(Math.random() * maxJitter)));
        await sync(retries - 1, delay * 2);
    }
}

/**
 * @returns {Number}
 */
export function now() {
    return Date.now() + offsetMs;
}
