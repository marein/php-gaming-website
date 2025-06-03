const globalConfig = document.querySelector('meta[name="server-time-config"]');
const url = globalConfig?.getAttribute('data-url') || '/api/server-time';
let offsetMs = 0;

sync();

export async function sync() {
    const clientTime = Date.now();
    const response = await fetch(url);
    const serverTime = await response.json();
    const roundTripTime = Date.now() - clientTime;
    const roundTripTimeMidpoint = clientTime + roundTripTime / 2;
    offsetMs = new Date(serverTime) - roundTripTimeMidpoint;
}

/**
 * @returns {Date}
 */
export function now() {
    return new Date(Date.now() + offsetMs);
}
