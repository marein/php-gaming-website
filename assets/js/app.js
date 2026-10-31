import htmx from 'htmx.org'
import {client} from './Common/HttpClient.js'
import './Common/NotificationList.js'
import '@tabler/core/dist/css/tabler.min.css'
import '../css/app.css'

window.fetch = (fetch => async (resource, options = {}) => {
    const response = await fetch(
        resource,
        {...options, headers: {...(options.headers || {}), 'X-Requested-With': 'XMLHttpRequest'}}
    );

    if (!options.headers?.['HX-Request'] && response.headers.has('HX-Trigger')) {
        Object.entries(JSON.parse(response.headers.get('HX-Trigger')))
            .forEach(([name, detail]) => htmx.trigger(document.body, name, detail));
    }

    return response;
})(window.fetch);

window.app = {
    navigate: (url, target = document.body) => htmx.ajax('GET', url, {target, push: 'true'}),
    loadElements: (node, timeout = 500) => {
        let timerId;

        return Promise.race([
            Promise.allSettled([...node.querySelectorAll(':not(:defined)')]
                .filter(n => !window.customElements.get(n.localName))
                .map(n => import(n.localName))),
            new Promise(r => timerId = setTimeout(r, timeout))
        ]).finally(() => clearTimeout(timerId));
    },
    showProgress(delay) {
        document.querySelector('.gp-page-progress')?.remove();
        const progress = document.createElement('div');
        progress.classList.add('gp-page-progress');
        const timeout = setTimeout(() => document.head.after(progress), delay ?? 250);
        return () => clearTimeout(timeout) || progress.classList.add('gp-page-progress--finish');
    },
    notifyUser(message, type, timeout = 3000) {
        document.querySelector('notification-list')?.appendMessage(message, type, timeout)
    }
}

client.onError = response => window.app.notifyUser(response.message, 'warning');

new MutationObserver(m => m.flatMap(m => [...m.addedNodes])
    .filter(n => n.nodeType === Node.ELEMENT_NODE)
    .forEach(window.app.loadElements)
).observe(document, {subtree: true, childList: true});

const isPageNavigation = ctx => ctx.target?.matches('body');
document.addEventListener('htmx:before:request', e => {
    const ctx = e.detail.ctx;
    const fetch = ctx.fetch;
    ctx.fetch = async (input, init) => {
        const response = await fetch(input, init);

        await window.app.loadElements(new DOMParser().parseFromString(await response.clone().text(), 'text/html').body);
        ctx.request.signal?.throwIfAborted();

        return response;
    };
});
document.addEventListener('htmx:before:request', e => {
    const ctx = e.detail.ctx;
    if (!isPageNavigation(ctx)) return;

    const buttons = ctx.sourceElement.matches('form') ? [...ctx.sourceElement.querySelectorAll('button')] : [];
    buttons.forEach(b => b.disabled = true);
    buttons.forEach(b => b.classList.add('btn-loading'));

    ctx.appNavigation = {finishProgress: window.app.showProgress(0), buttons};
});
document.addEventListener('htmx:finally:request', e => {
    const navigation = e.detail.ctx.appNavigation;
    if (!navigation) return;

    navigation.finishProgress();
    navigation.buttons.forEach(b => b.disabled = false);
    navigation.buttons.forEach(b => b.classList.remove('btn-loading'));
});
document.addEventListener('htmx:config:request', e => {
    const ctx = e.detail.ctx;
    if (!isPageNavigation(ctx)) return;

    const version = document.querySelector('meta[name="asset-version"]')?.content;
    if (version) ctx.request.headers['X-Asset-Version'] = version;
});
document.addEventListener('htmx:before:response', e => {
    const ctx = e.detail.ctx;
    if (ctx.response.status !== 409 || !ctx.response.headers.has('X-Asset-Version')) return;

    e.preventDefault();
    ctx.response.raw.url === location.href.split('#')[0] ? top.location.reload() : top.location.href = ctx.response.raw.url;
});
document.addEventListener('htmx:after:swap', () => window.dispatchEvent(new CustomEvent('app:load')));
document.addEventListener('htmx:before:history:update', e => {
    const {sourceElement, response} = e.detail;

    if (!sourceElement.matches?.('form')) return;
    if (sourceElement.method === 'get') return;
    if (response?.raw?.redirected) return;

    e.preventDefault();
});

window.matchMedia("(prefers-color-scheme:dark)").addEventListener(
    'change',
    e => document.documentElement.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light')
);

await window.app.loadElements(document.body).finally(window.app.showProgress());

document.addEventListener('change', e => {
    if (!e.target.matches('.gp-dropdown-toggle[type="checkbox"]')) return;

    const onClick = eClick => {
        if (eClick.target === e.target) return;
        if (eClick.target.closest(`label[for="${e.target.id}"]`)) return;

        e.target.checked = e.target.parentElement.contains(eClick.target);

        if (e.target.checked) document.addEventListener('click', onClick, {once: true});
    }

    if (e.target.checked) setTimeout(() => document.addEventListener('click', onClick, {once: true}), 0);
});

window.dispatchEvent(new CustomEvent('app:load'));
