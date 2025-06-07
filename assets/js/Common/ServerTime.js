const globalConfig = document.querySelector('meta[name="server-time-config"]');
const url = globalConfig?.getAttribute('data-url') || '/api/server-time';
let offsetMs = 0;

sync();

export async function sync() {
    const requestTime = Date.now();
    const response = await fetch(url);
    const serverTime = await response.json();
    const roundTripTime = Date.now() - requestTime;
    const timeWhenServerCreatedServerTime = requestTime + roundTripTime / 2;
    offsetMs = serverTime - timeWhenServerCreatedServerTime;
}

/**
 * @returns {int}
 */
export function now() {
    return Date.now() + offsetMs;
}
