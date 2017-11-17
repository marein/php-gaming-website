var Gambling = Gambling || {};
Gambling.ConnectFour = Gambling.ConnectFour || {};

Gambling.ConnectFour.GameService = class
{
    constructor()
    {

    }

    /**
     * @param {String} gameId
     */
    redirectTo(gameId)
    {
        top.location.href = '/game/' + gameId;
    }

    /**
     * @param {String} gameId
     * @param {int} column
     * @returns {Promise}
     */
    move(gameId, column)
    {
        return this.send(
            'POST',
            '/api/connect-four/games/' + gameId + '/move',
            'column=' + encodeURIComponent(column)
        );
    }

    /**
     * @returns {Promise}
     */
    open()
    {
        return this.send(
            'POST',
            '/api/connect-four/games/open',
            ''
        );
    }

    /**
     * @param {String} gameId
     * @returns {Promise}
     */
    abort(gameId)
    {
        return this.send(
            'POST',
            '/api/connect-four/games/' + gameId + '/abort',
            ''
        );
    }

    /**
     * @param {String} gameId
     * @returns {Promise}
     */
    join(gameId)
    {
        return this.send(
            'POST',
            '/api/connect-four/games/' + gameId + '/join',
            ''
        );
    }

    /**
     * @param {String} method
     * @param {String} url
     * @param {String} data
     * @returns {Promise}
     */
    send(method, url, data)
    {
        return new Promise((resolve, reject) => {
            let request = new XMLHttpRequest();
            request.open(method, url);
            if (data !== '') {
                request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            }
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.addEventListener('load', () => {
                let response = JSON.parse(request.responseText);

                if (request.status >= 200 && request.status < 300) {
                    resolve(response);
                } else {
                    app.notification.appendMessage(response.message);
                    reject(response);
                }
            });
            request.send(data);
        });
    }
};
