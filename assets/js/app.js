import {client} from './Common/HttpClient.js'
import './Common/NotificationList.js'
import '@tabler/core/dist/css/tabler.min.css'
import '../css/app.css'

window.fetch = (fetch => (resource, options = {}) => fetch(
    resource,
    {...options, headers: {...(options.headers || {}), 'X-Requested-With': 'XMLHttpRequest'}}
))(window.fetch);

window.app = {
    navigate: url => top.location.href = url,
    loadElements: node => Promise.allSettled([...node.querySelectorAll(':not(:defined)')]
        .filter(n => !window.customElements.get(n.localName))
        .map(n => import(n.localName))),
    showProgress(delay) {
        document.querySelector('.gp-page-progress')?.remove();
        const progress = document.createElement('div');
        progress.classList.add('gp-page-progress');
        const timeout = setTimeout(() => document.head.after(progress), delay ?? 250);
        return () => clearTimeout(timeout) || progress.classList.add('gp-page-progress--finish');
    },
    notifyUser(message, type, timeout = 3000) {
        document.querySelector('notification-list')?.appendMessage(message, type, timeout)
    },
    peInit() {
        if (!window.pe) return window.addEventListener('pe:init', window.app.peInit);
        window.app.navigate = window.pe.navigate;
        window.pe.selectSource = window.pe.selectTarget = d => d.querySelector('[data-page-content]');
    }
}

client.onError = response => window.app.notifyUser(response.message, 'warning');

window.app.peInit();
window.addEventListener('pe:click', e => e.detail.a.closest('[data-no-turbolink]') && e.preventDefault());
window.addEventListener('pe:navigate', e => {
    e.detail.parsed.push(dom => window.app.loadElements(dom.body));
    e.detail.succeed.push(() => window.dispatchEvent(new CustomEvent('app:load')));
    e.detail.finally.push(window.app.showProgress(0));
});
window.addEventListener('pe:include', e => {
    e.detail.parsed.push(dom => window.app.loadElements(dom.body));
});
window.addEventListener('pe:form', e => {
    e.detail.form.querySelectorAll('button').forEach(b => b.disabled = true);
    e.detail.form.querySelectorAll('button').forEach(b => b.classList.add('btn-loading'));

    e.detail.parsed.push(dom => window.app.loadElements(dom.body));
    e.detail.succeed.push(() => window.dispatchEvent(new CustomEvent('app:load')));
    e.detail.finally.push(window.app.showProgress(0));
    e.detail.finally.push(() => {
        e.detail.form.querySelectorAll('button').forEach(b => b.disabled = false);
        e.detail.form.querySelectorAll('button').forEach(b => b.classList.remove('btn-loading'));
    })
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
