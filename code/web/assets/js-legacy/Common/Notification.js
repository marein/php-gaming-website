var Gaming = Gaming || {};
Gaming.Common = Gaming.Common || {};

Gaming.Common.Notification = class
{
    /**
     * @param {Node} element
     */
    constructor(element)
    {
        this.element = element;
    }

    /**
     * @param {String} message
     */
    appendMessage(message)
    {
        let messageNode = this.createMessageNode(message);

        this.element.insertBefore(messageNode, this.element.childNodes[0]);

        // Show the message for 3 seconds, fade it out and remove the node.
        setTimeout(() => {
            messageNode.classList.add('notification__message--close');
            setTimeout(() => {
                this.element.removeChild(messageNode);
            }, 1000);
        }, 3000);
    }

    /**
     * @param {String} message
     * @returns {Node}
     */
    createMessageNode(message)
    {
        let smiley = document.createTextNode('¯\\_(ツ)_/¯');
        let br = document.createElement('br');
        let text = document.createTextNode(message);

        let li = document.createElement('li');
        li.classList.add('notification__message');

        li.append(smiley);
        li.append(br);
        li.append(text);

        return li;
    }
};
