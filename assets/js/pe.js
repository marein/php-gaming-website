function render(oldDocument, newDocument) {
    oldDocument.title = newDocument.title || oldDocument.title;
    oldDocument.body.replaceWith(newDocument.body);
    [...oldDocument.body.getElementsByTagName('script')].forEach(n => {
        const s = oldDocument.createElement('script');
        s.innerHTML = n.innerHTML;
        [...n.attributes].forEach(a => s.setAttribute(a.nodeName, a.nodeValue));
        n.replaceWith(s);
    });
}

function scroll(id) {
    const autofocus = document.querySelector('[autofocus]');
    if (autofocus) return autofocus.focus();
    if (!id) return window.scrollTo(0, 0);
    const element = document.getElementById(id);
    if (!element) return window.scrollTo(0, 0);
    element.scrollIntoView();
}

async function navigate(url, changeHistory) {
    window.pe.abortController.abort();
    const abortController = window.pe.abortController = new AbortController();

    const event = new CustomEvent('pe:navigate', {
        detail: {url, render, fetchOptions: {}, parsed: [], succeed: [], catch: [], finally: []}
    });
    window.dispatchEvent(event);

    try {
        const response = await fetch(url, {
            signal: abortController.signal,
            ...event.detail.fetchOptions
        });

        const dom = new DOMParser().parseFromString(await response.text(), 'text/html');
        await Promise.all(event.detail.parsed.map(f => f(dom)));

        abortController.signal.throwIfAborted();

        event.detail.render(document, dom);

        const hash = url.split('#')[1];
        if (changeHistory) history.pushState(null, '', response.url + (hash ? '#' + hash : ''));
        scroll(hash);
        await Promise.all(event.detail.succeed.map(f => f()));
    } catch (e) {
        await Promise.all(event.detail.catch.map(f => f(e)));
        throw e;
    } finally {
        await Promise.all(event.detail.finally.map(f => f()));
    }
}

window.pe = {navigate: url => navigate(url, true), abortController: new AbortController()};

window.addEventListener('popstate', () => navigate(top.location.href, false));
document.addEventListener('click', e => {
    if (e.metaKey || e.altKey || e.shiftKey) return;
    let a = e.target.closest('a');
    if (!a) return;
    if (a.protocol !== window.location.protocol || a.host !== window.location.host) return;
    if (a.hasAttribute('target') || a.hasAttribute('download')) return;
    if (!window.dispatchEvent(new CustomEvent('pe:click', {detail: {a}, cancelable: true}))) return;

    e.preventDefault();
    navigate(a.href, true);
});

window.dispatchEvent(new CustomEvent('pe:init'));
