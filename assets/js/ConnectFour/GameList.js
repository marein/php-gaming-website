import {service} from './GameService.js'

customElements.define('connect-four-game-list', class extends HTMLElement {
    connectedCallback() {
        this._onDisconnect = [];

        this._playerId = this.getAttribute('player-id');
        this._maximumNumberOfGamesInList = parseInt(this.getAttribute('maximum-number-of-games'));
        this._currentGamesInList = [];
        this._pendingGamesToRemove = [];
        this._pendingGamesToAdd = JSON.parse(this.getAttribute("open-games"));
        this._renderListTimeout = null;

        this._registerEventHandler();
        this._flushPendingGamesToAdd();
    }

    disconnectedCallback() {
        this._onDisconnect.forEach(f => f());
    }

    /**
     * @param {String} gameId
     * @param {String} playerId
     */
    _addGame(gameId, playerId) {
        if (this._currentGamesInList.indexOf(gameId) === -1) {
            this.querySelector('tbody').appendChild(
                this._createGameNode(gameId, this._playerId === playerId)
            );
        }
    }

    /**
     * @param {String} gameId
     */
    _removeGame(gameId) {
        if (this._currentGamesInList.indexOf(gameId) !== -1) {
            this.querySelector('tbody').removeChild(this.querySelector('[data-game-id="' + gameId + '"]'));
        }
    }

    /**
     * @param {String} gameId
     */
    _scheduleRemovingOfGame(gameId) {
        let indexOfGameInList = this._currentGamesInList.indexOf(gameId);

        if (indexOfGameInList !== -1) {
            this._pendingGamesToRemove.push(gameId);
            this._markGameAsToBeRemovedSoon(gameId);
        }

        this._pendingGamesToAdd = this._pendingGamesToAdd.filter((pendingGameToAdd) => {
            return pendingGameToAdd.gameId !== gameId;
        });

        if (this._renderListTimeout === null) {
            this._renderListTimeout = setTimeout(this._renderList.bind(this), 3000);
        }
    }

    /**
     * @param {String} gameId
     */
    _markGameAsToBeRemovedSoon(gameId) {
        if (this._currentGamesInList.indexOf(gameId) !== -1) {
            const row = this.querySelector('[data-game-id="' + gameId + '"]');
            row.classList.add('table-secondary', 'cursor-default');
            row.classList.remove('table-success', 'table-light');
        }
    }

    _flushPendingGamesToAdd() {
        // Limited by the maximum number of games in list.
        let limit = Math.min(
            this._pendingGamesToAdd.length,
            this._maximumNumberOfGamesInList - this._currentGamesInList.length
        );

        for (let i = 0; i < limit; i++) {
            let pendingGameToAdd = this._pendingGamesToAdd.shift();
            this._addGame(
                pendingGameToAdd.gameId,
                pendingGameToAdd.playerId
            );
            this._currentGamesInList.push(pendingGameToAdd.gameId);
        }
    }

    _flushPendingGamesToRemove() {
        let gamesToRemove = this._currentGamesInList.filter((gameId) => {
            let indexOfGameIdInRemoveList = this._pendingGamesToRemove.indexOf(gameId);
            return indexOfGameIdInRemoveList !== -1;
        });

        gamesToRemove.forEach((gameId) => {
            this._removeGame(gameId);
        });

        this._currentGamesInList = this._currentGamesInList.filter((gameId) => {
            let indexOfGameIdInRemoveList = this._pendingGamesToRemove.indexOf(gameId);
            return indexOfGameIdInRemoveList === -1;
        });

        this._pendingGamesToRemove = [];
    }

    async _renderList() {
        this._renderListTimeout = null;

        this.querySelector('tbody').classList.add('gp-loading');

        await new Promise(r => setTimeout(r, 250));

        this._flushPendingGamesToRemove();
        this._flushPendingGamesToAdd();
        this.querySelector('tbody').classList.remove('gp-loading');
    }

    /**
     * @param {String} gameId
     * @param {Boolean} isCurrentUserThePlayer
     * @returns {Node}
     */
    _createGameNode(gameId, isCurrentUserThePlayer) {
        let row = document.createElement('tr');
        row.dataset.gameId = gameId;
        row.innerHTML = '<td>Anonymous</td><td>???</td>';

        row.classList.toggle('table-success', isCurrentUserThePlayer);
        row.classList.toggle('table-light', !isCurrentUserThePlayer);

        row.addEventListener('click', (event) => {
            event.preventDefault();

            if (row.classList.contains('table-secondary') || row.closest('.gp-loading')) return;

            row.classList.add('table-secondary', 'cursor-default');
            row.classList.remove('table-success', 'table-light');

            if (isCurrentUserThePlayer) {
                service.abort(gameId)
                    .then(() => true)
                    .catch(() => {
                        // Remove the game on any error.
                        this._scheduleRemovingOfGame(gameId);
                    });
            } else {
                service.join(gameId)
                    .then(() => service.redirectTo(gameId))
                    .catch(() => {
                        // Remove the game on any error.
                        this._scheduleRemovingOfGame(gameId);
                    });
            }
        });

        return row;
    }

    _onGameOpened(event) {
        let gameId = event.detail.gameId;
        let playerId = event.detail.playerId;
        let pendingGameToAdd = {
            gameId: gameId,
            playerId: playerId
        };

        if (this._currentGamesInList.length < this._maximumNumberOfGamesInList) {
            this._pendingGamesToAdd.push(pendingGameToAdd);
            // Add game to list if field is after short amount of time still
            // in pendingGamesToAdd. Games which are opened and immediately
            // finished, take away space for open games. This is especially
            // useful on page refresh, because nchan republish messages in buffer
            // to new subscribers and hold that buffer for a short amount of time.
            setTimeout(() => {
                let indexOfGameIdInAddList = this._pendingGamesToAdd.indexOf(pendingGameToAdd);
                let indexOfGameIdInCurrentList = this._currentGamesInList.indexOf(gameId);
                if (indexOfGameIdInAddList !== -1) {
                    this._pendingGamesToAdd.splice(indexOfGameIdInAddList, 1);
                    if (indexOfGameIdInCurrentList === -1) {
                        this._addGame(gameId, playerId);
                        this._currentGamesInList.push(gameId);
                    }
                }
            }, 50);
        } else {
            let indexOfGameIdInAddList = this._pendingGamesToAdd.indexOf(gameId);
            if (indexOfGameIdInAddList === -1) {
                this._pendingGamesToAdd.push(pendingGameToAdd);
            }
        }
    }

    _onPlayerJoinedOrGameAborted(event) {
        this._scheduleRemovingOfGame(event.detail.gameId);
    }

    _registerEventHandler() {
        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.GameOpened', this._onGameOpened.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.PlayerJoined', this._onPlayerJoinedOrGameAborted.bind(this));

        ((n, f) => {
            window.addEventListener(n, f);
            this._onDisconnect.push(() => window.removeEventListener(n, f));
        })('ConnectFour.GameAborted', this._onPlayerJoinedOrGameAborted.bind(this));
    }
});
