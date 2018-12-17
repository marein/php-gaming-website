var Gaming = Gaming || {};
Gaming.ConnectFour = Gaming.ConnectFour || {};

Gaming.ConnectFour.GameService = class
{
    /**
     * @param {HttpClient} httpClient
     */
    constructor(httpClient)
    {
        this.httpClient = httpClient;
    }

    /**
     * @param {String} gameId
     */
    redirectTo(gameId)
    {
        this.httpClient.redirectTo(
            '/game/' + gameId
        );
    }

    /**
     * @param {String} gameId
     * @param {int} column
     * @returns {Promise}
     */
    move(gameId, column)
    {
        return this.httpClient.post(
            '/api/connect-four/games/' + gameId + '/move',
            {
                column: column
            }
        );
    }

    /**
     * @returns {Promise}
     */
    open()
    {
        return this.httpClient.post(
            '/api/connect-four/games/open'
        );
    }

    /**
     * @param {String} gameId
     * @returns {Promise}
     */
    abort(gameId)
    {
        return this.httpClient.post(
            '/api/connect-four/games/' + gameId + '/abort',
        );
    }

    /**
     * @param {String} gameId
     * @returns {Promise}
     */
    resign(gameId)
    {
        return this.httpClient.post(
            '/api/connect-four/games/' + gameId + '/resign',
        );
    }

    /**
     * @param {String} gameId
     * @returns {Promise}
     */
    join(gameId)
    {
        return this.httpClient.post(
            '/api/connect-four/games/' + gameId + '/join',
        );
    }
};
