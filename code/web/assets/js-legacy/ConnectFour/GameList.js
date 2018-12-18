import { service } from '../../js/ConnectFour/GameService.js'

window.Gaming = window.Gaming || {};
window.Gaming.ConnectFour = window.Gaming.ConnectFour || {};

window.Gaming.ConnectFour.GameList = class
{
    /**
     * @param {Gaming.Common.EventPublisher} eventPublisher
     * @param {Node} games
     */
    constructor(eventPublisher, games)
    {
        this.eventPublisher = eventPublisher;
        this.games = games;
        this.maximumNumberOfGamesInList = parseInt(this.games.dataset.maximumNumberOfGames);
        this.currentGamesInList = [];
        this.pendingGamesToRemove = [];
        this.pendingGamesToAdd = JSON.parse(this.games.dataset.openGames);
        this.renderListTimeout = null;

        this.registerEventHandler();
        this.flushPendingGamesToAdd();
    }

    /**
     * @param {String} gameId
     * @param {String} playerId
     */
    addGame(gameId, playerId)
    {
        if (this.currentGamesInList.indexOf(gameId) === -1) {
            let isCurrentUserThePlayer = app.user.id === playerId;

            this.games.appendChild(
                this.createGameNode(gameId, isCurrentUserThePlayer)
            );
        }
    }

    /**
     * @param {String} gameId
     */
    removeGame(gameId)
    {
        if (this.currentGamesInList.indexOf(gameId) !== -1) {
            let node = this.games.querySelector('[data-game-id="' + gameId + '"]');
            this.games.removeChild(node);
        }
    }

    /**
     * @param {String} gameId
     */
    markGameAsToBeRemovedSoon(gameId)
    {
        if (this.currentGamesInList.indexOf(gameId) !== -1) {
            let node = this.games.querySelector('[data-game-id="' + gameId + '"]');
            node.classList.add('game-list__game--remove-soon');
            node.classList.remove('game-list__game--user-game');
        }
    }

    /**
     * @param {Node} game
     * @param {Boolean} isCurrentUserThePlayer
     */
    addChildComponents(game, isCurrentUserThePlayer)
    {
        if (isCurrentUserThePlayer) {
            new Gaming.ConnectFour.AbortGameButton(
                game.querySelector('button')
            );
        } else {
            new Gaming.ConnectFour.JoinGameButton(
                game.querySelector('button')
            );
        }
    }

    flushPendingGamesToAdd()
    {
        // Limited by the maximum number of games in list.
        let limit = Math.min(
            this.pendingGamesToAdd.length,
            this.maximumNumberOfGamesInList - this.currentGamesInList.length
        );

        for (let i = 0; i < limit; i++) {
            let pendingGameToAdd = this.pendingGamesToAdd.pop();
            this.addGame(
                pendingGameToAdd.gameId,
                pendingGameToAdd.playerId
            );
            this.currentGamesInList.push(pendingGameToAdd.gameId);
        }
    }

    flushPendingGamesToRemove()
    {
        let gamesToRemove = this.currentGamesInList.filter((gameId) => {
            let indexOfGameIdInRemoveList = this.pendingGamesToRemove.indexOf(gameId);
            return indexOfGameIdInRemoveList !== -1;
        });

        gamesToRemove.forEach((gameId) => {
            this.removeGame(gameId);
        });

        this.currentGamesInList = this.currentGamesInList.filter((gameId) => {
            let indexOfGameIdInRemoveList = this.pendingGamesToRemove.indexOf(gameId);
            return indexOfGameIdInRemoveList === -1;
        });

        this.pendingGamesToRemove = [];
    }

    renderList()
    {
        this.renderListTimeout = null;
        this.games.classList.add('loading-indicator');

        // Freeze the screen.
        setTimeout(() => {
            this.flushPendingGamesToRemove();
            this.flushPendingGamesToAdd();
            this.games.classList.remove('loading-indicator');
        }, 250);
    }

    /**
     * @param {String} gameId
     * @param {Boolean} isCurrentUserThePlayer
     * @returns {Node}
     */
    createGameNode(gameId, isCurrentUserThePlayer)
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

        this.addChildComponents(li, isCurrentUserThePlayer);

        return li;
    }

    onGameOpened(event)
    {
        let gameId = event.payload.gameId;
        let playerId = event.payload.playerId;
        let pendingGameToAdd = {
            gameId: gameId,
            playerId: playerId
        };

        if (this.currentGamesInList.length < this.maximumNumberOfGamesInList) {
            this.pendingGamesToAdd.push(pendingGameToAdd);
            // Add game to list if field is after short amount of time still
            // in pendingGamesToAdd. Games which are opened and immediately
            // finished, take away space for open games. This is especially
            // useful on page refresh, because nchan republish messages in buffer
            // to new subscribers and hold that buffer for a short amount of time.
            setTimeout(() => {
                let indexOfGameIdInAddList = this.pendingGamesToAdd.indexOf(pendingGameToAdd);
                let indexOfGameIdInCurrentList = this.currentGamesInList.indexOf(gameId);
                if (indexOfGameIdInAddList !== -1) {
                    this.pendingGamesToAdd.splice(indexOfGameIdInAddList, 1);
                    if (indexOfGameIdInCurrentList === -1) {
                        this.addGame(gameId, playerId);
                        this.currentGamesInList.push(gameId);
                    }
                }
            }, 50);
        } else {
            let indexOfGameIdInAddList = this.pendingGamesToAdd.indexOf(gameId);
            if (indexOfGameIdInAddList === -1) {
                this.pendingGamesToAdd.push(pendingGameToAdd);
            }
        }
    }

    onPlayerJoinedOrGameAborted(event)
    {
        let gameId = event.payload.gameId;
        let indexOfGameInList = this.currentGamesInList.indexOf(gameId);

        if (indexOfGameInList !== -1) {
            this.pendingGamesToRemove.push(gameId);
            this.markGameAsToBeRemovedSoon(gameId);
        }

        this.pendingGamesToAdd = this.pendingGamesToAdd.filter((pendingGameToAdd) => {
            return pendingGameToAdd.gameId !== gameId;
        });

        if (this.renderListTimeout === null) {
            this.renderListTimeout = setTimeout(this.renderList.bind(this), 3000);
        }
    }

    registerEventHandler()
    {
        this.eventPublisher.subscribe({
            isSubscribedTo: (event) => {
                return event.name === 'ConnectFour.GameOpened';
            },
            handle: this.onGameOpened.bind(this)
        });

        this.eventPublisher.subscribe({
            isSubscribedTo: (event) => {
                return [
                    'ConnectFour.PlayerJoined',
                    'ConnectFour.GameAborted'
                ].indexOf(event.name) !== -1;
            },
            handle: this.onPlayerJoinedOrGameAborted.bind(this)
        });
    }
};
