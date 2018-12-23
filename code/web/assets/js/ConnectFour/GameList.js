import { service } from './GameService.js'

class GameListElement extends HTMLElement
{
    connectedCallback()
    {
        this._games = document.createElement('ul');
        this._games.classList.add('game-list');

        this.append(this._games);

        this._playerId = this.getAttribute('player-id');
        this._maximumNumberOfGamesInList = parseInt(this.getAttribute('maximum-number-of-games'));
        this._currentGamesInList = [];
        this._pendingGamesToRemove = [];
        this._pendingGamesToAdd = JSON.parse(this.getAttribute("open-games"));
        this._renderListTimeout = null;

        this._registerEventHandler();
        this._flushPendingGamesToAdd();
    }

    /**
     * @param {String} gameId
     * @param {String} playerId
     */
    _addGame(gameId, playerId)
    {
        if (this._currentGamesInList.indexOf(gameId) === -1) {
            let isCurrentUserThePlayer = this._playerId === playerId;

            this._games.appendChild(
                this._createGameNode(gameId, isCurrentUserThePlayer)
            );
        }
    }

    /**
     * @param {String} gameId
     */
    _removeGame(gameId)
    {
        if (this._currentGamesInList.indexOf(gameId) !== -1) {
            let node = this._games.querySelector('[data-game-id="' + gameId + '"]');
            this._games.removeChild(node);
        }
    }

    /**
     * @param {String} gameId
     */
    _scheduleRemovingOfGame(gameId)
    {
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
    _markGameAsToBeRemovedSoon(gameId)
    {
        if (this._currentGamesInList.indexOf(gameId) !== -1) {
            let node = this._games.querySelector('[data-game-id="' + gameId + '"]');
            node.classList.add('game-list__game--remove-soon');
            node.classList.remove('game-list__game--user-game');
        }
    }

    _flushPendingGamesToAdd()
    {
        // Limited by the maximum number of games in list.
        let limit = Math.min(
            this._pendingGamesToAdd.length,
            this._maximumNumberOfGamesInList - this._currentGamesInList.length
        );

        for (let i = 0; i < limit; i++) {
            let pendingGameToAdd = this._pendingGamesToAdd.pop();
            this._addGame(
                pendingGameToAdd.gameId,
                pendingGameToAdd.playerId
            );
            this._currentGamesInList.push(pendingGameToAdd.gameId);
        }
    }

    _flushPendingGamesToRemove()
    {
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

    _renderList()
    {
        this._renderListTimeout = null;
        this._games.classList.add('loading-indicator');

        // Freeze the screen.
        setTimeout(() => {
            this._flushPendingGamesToRemove();
            this._flushPendingGamesToAdd();
            this._games.classList.remove('loading-indicator');
        }, 250);
    }

    /**
     * @param {String} gameId
     * @param {Boolean} isCurrentUserThePlayer
     * @returns {Node}
     */
    _createGameNode(gameId, isCurrentUserThePlayer)
    {
        let span = document.createElement('span');
        span.innerText = 'Anonymous';

        let button = document.createElement('button');
        button.dataset.gameId = gameId;

        let li = document.createElement('li');
        li.classList.add('game-list__game');
        li.dataset.gameId = gameId;

        if (isCurrentUserThePlayer) {
            li.classList.add('game-list__game--user-game');
        }

        button.append(span);
        li.append(button);

        button.addEventListener('click', (event) => {
            event.preventDefault();

            button.disabled = true;
            button.classList.add('loading-indicator');

            if (isCurrentUserThePlayer) {
                service.abort(gameId).then(() => {
                    button.classList.remove('loading-indicator');
                }).catch(() => {
                    // Remove the game on any error.
                    button.classList.remove('loading-indicator');
                    this._scheduleRemovingOfGame(gameId);
                });
            } else {
                service.join(gameId).then(() => {
                    service.redirectTo(gameId);
                }).catch(() => {
                    // Remove the game on any error.
                    button.classList.remove('loading-indicator');
                    this._scheduleRemovingOfGame(gameId);
                });
            }
        });

        return li;
    }

    _onGameOpened(event)
    {
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

    _onPlayerJoinedOrGameAborted(event)
    {
        this._scheduleRemovingOfGame(event.detail.gameId);
    }

    _registerEventHandler()
    {
        window.addEventListener(
            'ConnectFour.GameOpened',
            this._onGameOpened.bind(this)
        );

        window.addEventListener(
            'ConnectFour.PlayerJoined',
            this._onPlayerJoinedOrGameAborted.bind(this)
        );

        window.addEventListener(
            'ConnectFour.GameAborted',
            this._onPlayerJoinedOrGameAborted.bind(this)
        );
    }
}

customElements.define('connect-four-game-list', GameListElement);
