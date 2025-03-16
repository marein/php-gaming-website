import {client} from '../Common/HttpClient.js'

class GameService {
    /**
     * @param {HttpClient} httpClient
     */
    constructor(httpClient) {
        this.httpClient = httpClient;
    }

    /**
     * @param {String} gameId
     */
    redirectTo(gameId) {
        this.httpClient.redirectTo(
            '/game/' + gameId
        );
    }

    /**
     * @param {String} gameId
     * @param {int} column
     * @returns {Promise}
     */
    move(gameId, column) {
        return this.httpClient.post(
            '/api/connect-four/games/' + gameId + '/move',
            {column}
        );
    }

    /**
     * @param {String} gameId
     * @returns {Promise}
     */
    abort(gameId) {
        return this.httpClient.post(
            '/api/connect-four/games/' + gameId + '/abort',
        );
    }

    /**
     * @param {String} gameId
     * @returns {Promise}
     */
    resign(gameId) {
        return this.httpClient.post(
            '/api/connect-four/games/' + gameId + '/resign',
        );
    }

    /**
     * @param {String} gameId
     * @returns {Promise}
     */
    join(gameId) {
        return this.httpClient.post(
            '/api/connect-four/games/' + gameId + '/join',
        );
    }
}

export const service = new GameService(client);
