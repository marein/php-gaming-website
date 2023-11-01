class HttpClient {
    constructor() {
        this.onError = (response) => {
        };
    }

    /**
     * @param {String} url
     * @returns {Promise}
     */
    async get(url) {
        const response = await fetch(url, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });
        const json = await response.json();

        if (response.status >= 200 && response.status < 300) return json;

        this.onError(json);
        throw json;
    }

    /**
     * @param {String} url
     * @param {Object} data
     * @returns {Promise}
     */
    async post(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: new URLSearchParams(data)
        });
        const json = await response.json();

        if (response.status >= 200 && response.status < 300) return json;

        this.onError(json);
        throw json;
    }

    /**
     * @param {String} url
     */
    redirectTo(url) {
        window.app.navigate(url);
    }
}

export const client = new HttpClient();
