import {html} from 'uhtml/node.js'

/**
 * @param {String} username
 * @returns {Node}
 */
export function createUsernameNode(username) {
    const [handle, ...tag] = username.split('#');

    return tag.length ? html`${handle}<span class="gp-id-username-tag">#${tag.join('#')}</span>` : html`${handle}`;
}
