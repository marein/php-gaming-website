/**
 * We don't use a bundler.
 * This is intentional because we use ECMAScript 6 modules and we want to keep the tooling small.
 */
import './Chat/Widget.js'

import './Common/EventSource.js'
import { client } from './Common/HttpClient.js';
import './Common/NotificationList.js'

import './ConnectFour/Game.js'
import './ConnectFour/GameList.js'
import './ConnectFour/AbortGameButton.js'
import './ConnectFour/OpenGameButton.js'
import './ConnectFour/ResignGameButton.js'
import './ConnectFour/RunningGames.js'

const notificationListElement = document.querySelector('notification-list');

client.onError = (response) => {
    notificationListElement.appendMessage(response.message);
};
