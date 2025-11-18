import http from 'k6/http';

const baseUrl = __ENV.BASE_URL;
const pageUrl = __ENV.PAGE_URL;
const headers = {'Origin': baseUrl};
const cookieJar = new http.CookieJar();

export const options = {
    insecureSkipTLSVerify: true
};

export default function () {
    for (let i = 0; i < 100; i++) {
        http.get(baseUrl + (pageUrl || '/'), {}, {cookieJar, headers});
    }
}
