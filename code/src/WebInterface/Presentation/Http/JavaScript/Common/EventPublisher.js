var Gambling = Gambling || {};
Gambling.Common = Gambling.Common || {};

Gambling.Common.EventPublisher = class
{
    constructor()
    {
        this.subscribers = [];
    }

    /**
     * @param {{isSubscribedTo:Function, handle:Function}} subscriber
     */
    subscribe(subscriber)
    {
        this.subscribers.push(subscriber);
    }

    /**
     * @param {{name:String, payload:Object}} event
     */
    publish(event)
    {
        this.subscribers.forEach((subscriber) => {
            if (subscriber.isSubscribedTo(event)) {
                subscriber.handle(event);
            }
        });
    }
};
