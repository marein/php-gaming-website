import { client } from './Common/HttpClient.js'
import './Common/NotificationList.js'

window.app = {
    loadElements: node => Promise.allSettled([...node.querySelectorAll(':not(:defined)')]
        .filter(n => !window.customElements.get(n.localName))
        .map(n => import(n.localName))),
    showProgress() {
        document.querySelector('.progress')?.remove();
        let progress = document.createElement('div');
        progress.classList.add('progress');
        const timeout = setTimeout(() => document.head.after(progress), 250);
        return () => clearTimeout(timeout) || progress.classList.add('progress--finish');
    }
}

client.onError = response => document.querySelector('notification-list').appendMessage(response.message);

await window.app.loadElements(document.body).finally(window.app.showProgress());

window.dispatchEvent(new CustomEvent('app:load'));
