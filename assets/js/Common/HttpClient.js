class HttpClient
{
    constructor()
    {
        this.onError = (response) => {

        };
    }

    /**
     * @param {String} url
     * @returns {Promise}
     */
    get(url)
    {
        return new Promise((resolve, reject) => {
            let request = new XMLHttpRequest();
            request.open('GET', url);
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.addEventListener('load', () => {
                let response = JSON.parse(request.responseText);

                if (request.status >= 200 && request.status < 300) {
                    resolve(response);
                } else {
                    this.onError(response);
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
            request.open('POST', url);
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.addEventListener('load', () => {
                let response = JSON.parse(request.responseText);

                if (request.status >= 200 && request.status < 300) {
                    resolve(response);
                } else {
                    this.onError(response);
                    reject(response);
                }
            });
            request.send(
                this._preparePostParameters(postParameters)
            );
        });
    }

    /**
     * @param {String} url
     */
    redirectTo(url)
    {
        top.location.href = url;
    }

    /**
     * @param {Object} postParameters
     * @returns {String}
     */
    _preparePostParameters(postParameters)
    {
        let preparedPostParameters = [];

        for (let name in postParameters) {
            let value = postParameters[name];
            preparedPostParameters.push(name + '=' + encodeURIComponent(value));
        }

        return preparedPostParameters.join('&');
    }
}

export const client = new HttpClient();
