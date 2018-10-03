var Gaming = Gaming || {};
Gaming.Common = Gaming.Common || {};

/**
 * This class handles the CSRF protection required by the web interface context.
 *
 * todo: Leverage the fetch api.
 */
Gaming.Common.HttpClient = class
{
    /**
     * @param {String} baseUrl
     * @param {Gaming.Common.Notification} notification
     */
    constructor(baseUrl, notification)
    {
        this.baseUrl = baseUrl;
        this.csrfToken = this.readCsrfTokenFromCookie();
        this.notification = notification;
    }

    /**
     * @param {String} url
     * @returns {Promise}
     */
    get(url)
    {
        return new Promise((resolve, reject) => {
            let request = new XMLHttpRequest();
            request.open('GET', this.baseUrl + url);
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.addEventListener('load', () => {
                let response = JSON.parse(request.responseText);

                if (request.status >= 200 && request.status < 300) {
                    resolve(response);
                } else {
                    this.notification.appendMessage(response.message);
                    reject(response);
                }
            });
            request.send();
        });
    }

    /**
     * @param {String} url
     * @param {Object} [postParameters]
     * @returns {Promise}
     */
    post(url, postParameters)
    {
        postParameters = postParameters || {};

        return new Promise((resolve, reject) => {
            let request = new XMLHttpRequest();
            request.open('POST', this.baseUrl + url);
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.setRequestHeader('X-XSRF-TOKEN', this.csrfToken);
            request.addEventListener('load', () => {
                let response = JSON.parse(request.responseText);

                if (request.status >= 200 && request.status < 300) {
                    resolve(response);
                } else {
                    this.notification.appendMessage(response.message);
                    reject(response);
                }
            });
            request.send(
                this.preparePostParameters(postParameters)
            );
        });
    }

    /**
     * @param {String} url
     */
    redirectTo(url)
    {
        top.location.href = this.baseUrl + url;
    }

    /**
     * @param {Object} postParameters
     * @returns {String}
     */
    preparePostParameters(postParameters)
    {
        let preparedPostParameters = [];

        for (let name in postParameters) {
            let value = postParameters[name];
            preparedPostParameters.push(name + '=' + encodeURIComponent(value));
        }

        return preparedPostParameters.join('&');
    }

    /**
     * @returns {String}
     */
    readCsrfTokenFromCookie()
    {
        let matches = document.cookie.match(/XSRF-TOKEN=([^;]+);?/);
        return matches[1];
    }
};
