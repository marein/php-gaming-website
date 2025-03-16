import http from 'k6/http';

const baseUrl = __ENV.BASE_URL;
const headers = {'Origin': baseUrl};
const jarPlayerOne = new http.CookieJar();
const jarPlayerTwo = new http.CookieJar();

export default function () {
    const gameId = open(jarPlayerOne);
    join(jarPlayerTwo, gameId);
    move(jarPlayerOne, gameId, 1);
    move(jarPlayerTwo, gameId, 2);
    move(jarPlayerOne, gameId, 3);
    move(jarPlayerTwo, gameId, 4);
    move(jarPlayerOne, gameId, 5);
    move(jarPlayerTwo, gameId, 6);
    move(jarPlayerOne, gameId, 7);
    move(jarPlayerTwo, gameId, 1);
    move(jarPlayerOne, gameId, 2);
    move(jarPlayerTwo, gameId, 3);
    move(jarPlayerOne, gameId, 4);
    move(jarPlayerTwo, gameId, 5);
    move(jarPlayerOne, gameId, 6);
    move(jarPlayerTwo, gameId, 7);
    move(jarPlayerOne, gameId, 1);
    move(jarPlayerTwo, gameId, 2);
    move(jarPlayerOne, gameId, 3);
    move(jarPlayerTwo, gameId, 4);
    move(jarPlayerOne, gameId, 5);
    move(jarPlayerTwo, gameId, 6);
    move(jarPlayerOne, gameId, 7);
    move(jarPlayerTwo, gameId, 1);
}

function open(jar) {
    let url = `${baseUrl}/api/connect-four/games/open`;
    let response = http.post(
        url,
        {'open[size]': '7x6', 'open[variant]': 'standard', 'open[color]': '1'},
        {jar, headers, redirects: 0}
    );
    return response.headers['Location'].match(/\/challenge\/(.*)$/)[1];
}

function join(jar, gameId) {
    let url = `${baseUrl}/api/connect-four/games/${gameId}/join`;
    return http.post(url, {}, {jar, headers});
}

function move(jar, gameId, column) {
    let url = `${baseUrl}/api/connect-four/games/${gameId}/move`;
    return http.post(url, {column}, {jar, headers});
}
