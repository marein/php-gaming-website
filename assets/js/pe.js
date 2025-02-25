function render(oldDocument, newDocument) {
    oldDocument.title = newDocument.title || oldDocument.title;
    const sourceNode = window.pe.selectSource(newDocument);
    window.pe.selectTarget(oldDocument).replaceWith(sourceNode);
    [...sourceNode.getElementsByTagName('script')].forEach(n => {
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

function mergeSearchParams(url, searchParams) {
    url = new URL(url);
    searchParams.forEach((value, key) => url.searchParams.append(key, value));
    return url.toString();
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

async function submit(form) {
    window.pe.abortController.abort();
    const abortController = window.pe.abortController = new AbortController();

    const event = new CustomEvent('pe:form', {
        detail: {form, render, fetchOptions: {}, parsed: [], succeed: [], catch: [], finally: []}
    });
    window.dispatchEvent(event);

    const formData = new FormData(form);
    const url = form.method === 'get' ? mergeSearchParams(form.action, formData) : form.action;

    try {
        const response = await fetch(url, {
            signal: abortController.signal,
            method: form.method,
            body: form.method === 'post' ? formData : null,
            ...event.detail.fetchOptions
        });

        const dom = new DOMParser().parseFromString(await response.text(), 'text/html');
        await Promise.all(event.detail.parsed.map(f => f(dom)));

        abortController.signal.throwIfAborted();

        event.detail.render(document, dom);

        if (response.redirected || form.method === 'get') history.pushState(null, '', response.url);
        scroll(form.action.split('#')[1] ?? form.getAttribute('id'));
        await Promise.all(event.detail.succeed.map(f => f()));
    } catch (e) {
        await Promise.all(event.detail.catch.map(f => f(e)));
        throw e;
    } finally {
        await Promise.all(event.detail.finally.map(f => f()));
    }
}

window.fetch = (fetch => async (resource, options = {}) => {
    const headers = {'Pe-Request': '1', ...(options.headers || {})};
    const response = await fetch(resource, {...options, headers});

    JSON.parse(response.headers.get('Pe-Dispatch') ?? '[]').forEach(e => {
        window.dispatchEvent(new CustomEvent(e.name, {detail: e.detail}));
    });

    return !response.headers.has('Pe-Location')
        ? response
        : new Proxy(await window.fetch(response.headers.get('Pe-Location'), {headers}), {
            get: (target, prop) => {
                if (prop === 'redirected') return true;

                const value = Reflect.get(target, prop, target);

                return typeof value === 'function' ? value.bind(target) : value;
            }
        });
})(window.fetch);

customElements.define('pe-include', class extends HTMLElement {
    async connectedCallback() {
        const url = this.getAttribute('src');
        const delay = this.getAttribute('delay') || 0;

        const event = new CustomEvent('pe:include', {
            detail: {url, fetchOptions: {}, parsed: [], succeed: [], catch: [], finally: []}
        });
        window.dispatchEvent(event);

        await new Promise(r => setTimeout(r, delay));

        try {
            const response = await fetch(url, event.detail.fetchOptions);

            const dom = new DOMParser().parseFromString(await response.text(), 'text/html');
            await Promise.all(event.detail.parsed.map(f => f(dom)));

            this.replaceWith(...dom.body.childNodes);

            await Promise.all(event.detail.succeed.map(f => f()));
        } catch (e) {
            await Promise.all(event.detail.catch.map(f => f(e)));
            throw e;
        } finally {
            await Promise.all(event.detail.finally.map(f => f()));
        }
    }
});

window.pe = {
    navigate: url => navigate(url, true),
    submit,
    abortController: new AbortController(),
    selectSource: d => d.body,
    selectTarget: d => d.body
};

window.addEventListener('popstate', () => navigate(top.location.href, false));
document.addEventListener('click', e => {
    if (e.metaKey || e.altKey || e.shiftKey) return;
    const a = e.target.closest('a');
    if (!a) return;
    if (a.protocol !== window.location.protocol || a.host !== window.location.host) return;
    if (a.hasAttribute('target') || a.hasAttribute('download')) return;
    if (!window.dispatchEvent(new CustomEvent('pe:click', {detail: {a}, cancelable: true}))) return;

    e.preventDefault();
    navigate(a.href, true);
});
document.addEventListener('submit', e => {
    if (!e.target.action.startsWith(window.location.origin.concat('/'))) return;
    if (e.target.hasAttribute('target')) return;
    if (!window.dispatchEvent(new CustomEvent('pe:submit', {detail: {form: e.target}, cancelable: true}))) return;

    e.preventDefault();
    submit(e.target);
});

window.dispatchEvent(new CustomEvent('pe:init'));
