var Gambling = Gambling || {};
Gambling.Common = Gambling.Common || {};

Gambling.Common.Template = class
{
    /**
     * @param {Node} node
     */
    constructor(node)
    {
        this.node = node;
    }

    /**
     * @param {String} selector
     * @returns {Gambling.Common.Template}
     */
    static fromSelector(selector)
    {
        return new Gambling.Common.Template(
            document.querySelector(selector)
        );
    }

    /**
     *
     * @param {Object} parameters
     * @returns {Node}
     */
    render(parameters)
    {
        let nodeClone = this.node.cloneNode(true);

        for (let name in parameters) {
            let value = parameters[name];
            nodeClone.innerHTML = nodeClone.innerHTML.replace(
                new RegExp('---' + name + '---', 'g'),
                value
            );
        }

        return document.importNode(nodeClone.content, true);
    }
};
