# php-gaming-website

__Table of contents__

* [Overview](#overview)
* [Installation and requirements](#installation-and-requirements)
* [Context is king](#context-is-king)
  * [Chat](#chat)
  * [Common](#common)
  * [Connect Four](#connect-four)
  * [Identity](#identity)
  * [Web Interface](#web-interface)
* [Transition to Microservices](#transition-to-microservices)
* [Scale-Out the application](#scale-out-the-application)
* [Chosen technologies](#chosen-technologies)
* [A note on testing](#a-note-on-testing)

## Overview

This is my playground project to explore new ideas and concepts. It's a gaming website where people can play against
each other. Currently, the only game is [Connect Four](#connect-four) but I plan other games to show more concepts.

Before you start looking at the code, I recommend reading this documentation to understand what concepts I use
and why I apply these concepts for this particular application.

The application is built with a
[Microservice Architecture](https://martinfowler.com/articles/microservices.html),
concepts of
[Domain Driven Design](http://domainlanguage.com/ddd/reference/)
and Scale-Out techniques in mind.
The sections
[Context is king](#context-is-king),
[Transition to Microservices](#transition-to-microservices)
and
[Scale-Out the application](#scale-out-the-application)
describe what's done to apply these concepts.

## Installation and requirements

[Docker](https://www.docker.com)
with
[Docker Compose](https://docs.docker.com/compose/)
plugin for deploying the application.

A modern browser, as this project uses modern features and doesn't polyfill all of them, e.g.
[Server-sent events](https://caniuse.com/eventsource)
and
[Custom Elements](https://caniuse.com/custom-elementsv1).

### Development

```
git clone https://github.com/marein/php-gaming-website
cd php-gaming-website
./project build
```

There're several other handy commands in the project script, like running tests or a static analyzer. You see them with

```
./project help
```

__Note__  
If you update your local repository, you have to "./project build" again.
This project is in development and I don't pollute the code with schema changes at this stage.

Following urls are accessible after the project is successfully started.

| URL                                              | Information                    |
|--------------------------------------------------|--------------------------------|
| [http://localhost/](http://localhost/)           | The application.               |
| [http://localhost:8081/](http://localhost:8081/) | MySQL management interface.    |
| [http://localhost:8082/](http://localhost:8082/) | Redis management interface.    |
| [http://localhost:8083/](http://localhost:8083/) | Grafana management interface.  |

### Production

The
[production images](https://hub.docker.com/r/marein/php-gaming-website/)
are built when pushed to git master. They always reflect the latest stable version.

You can run them as follows.

```
git clone https://github.com/marein/php-gaming-website
cd php-gaming-website
docker compose -f deploy/single-server/docker-compose.yml pull
docker compose -f deploy/single-server/docker-compose.yml up
```

Or you can try out
[Play with Docker](http://play-with-docker.com?stack=https://raw.githubusercontent.com/marein/php-gaming-website/master/deploy/single-server/docker-compose.yml).

## Context is king

The application is split into several
[Bounded Contexts](https://martinfowler.com/bliki/BoundedContext.html).
I've chosen modeling techniques like
[Domain Driven Design](http://domainlanguage.com/ddd/reference/)
and
[Event Storming](https://en.wikipedia.org/wiki/Event_storming)
to define them. Well, you only see the result and not the breakthroughs I went through.
The following sections describe what techniques I use in the respective context.
Note that I've intentionally chosen a different approach in each context.

I presuppose you've read
[Implementing Domain Driven Design](https://vaughnvernon.co/?page_id=168#iddd)
by Vaughn Vernon and
[Tackling Complexity in the Heart of Software](http://dddcommunity.org/book/evans_2003/)
by Eric Evans.

### Chat

To organize the business logic, the
[Chat](/src/Chat)
uses the 
[Transaction Script](https://martinfowler.com/eaaCatalog/transactionScript.html)
pattern. The tasks are to initiate chats, list messages from a chat, and allow authors to write messages in a chat.
If authors are assigned to a chat, only those authors can write and read messages.

The public interface is formed by a
[controller](/src/Chat/Presentation/Http/ChatController.php),
which can be called up via http, and a
[message handler](/src/Chat/Infrastructure/Messaging/CommandMessageHandler.php),
which serves as an interface to a message broker.

This context publishes
[Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
through the message broker to inform other contexts what's happened here.
First the domain events are stored to the event store.
This happens in the same transaction in which the commands are executed.
After that, an
[event subscriber](/src/Chat/Infrastructure/Messaging/PublishStoredEventsToMessageBrokerSubscriber.php)
publishes these stored events to the message broker.

I've chosen MySQL as the storage.

### Common

This
[Common](/src/Common)
folder provides reusable components. If the project is more advanced, I'll outsource them as libraries.
But there're already battle tested implementations out there (like a
[Bus](https://tactician.thephpleague.com)
by Tactician, or an
[Event Store](https://github.com/prooph/event-store)
by prooph). You may use them, instead of mine. The
[Event Store](/src/Common/EventStore)
implementation inside
[Common](/src/Common)
isn't used to be a storage for an
[Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html)
model. It's really just a storage for events.

### Connect Four

The
[Connect Four](/src/ConnectFour)
is the context where I put the most effort in. The business logic is definitely worth building a proper
[Domain Model](https://martinfowler.com/eaaCatalog/domainModel.html).
Players can open, join, abort and resign a game. Of course they can also perform moves.
The game can be aborted until the second move. After the second move, players can only resign or finish the game.
The referee, which sits near the game desks, ensure that the people can talk to each other.
This process is described below.

As the
[folder structure](/src/ConnectFour)
shows, this context uses the "Ports and Adapters" architecture. The
[Application Layer](https://martinfowler.com/eaaCatalog/serviceLayer.html)
is split into several callable classes. Cross-cutting concerns are handled via a decorated
[mediator](/src/Common/Bus/Bus.php)
instead of handling them directly inside the classes.

The public interface is formed by a
[controller](/src/ConnectFour/Port/Adapter/Http/GameController.php),
which can be called up via http.

This context publishes
[Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
through the message broker to inform other contexts what's happened here.
First the domain events are stored to the event store.
This happens in the same transaction in which the commands are executed.
After that, an
[event subscriber](/src/ConnectFour/Port/Adapter/Messaging/PublishStoredEventsToMessageBrokerSubscriber.php)
publishes these stored events to the message broker.

The Connect Four context applies the
[CQRS](https://martinfowler.com/bliki/CQRS.html)
pattern. Not only the domain model is divided into command and query side, but also the storage layer.
The query model is stored in an
[eventual consistency](https://en.wikipedia.org/wiki/Eventual_consistency)
manner. Multiple
[projections](/src/ConnectFour/Port/Adapter/Persistence/Projection)
retrieve the stored events from the event store and create the query models.
This is done for scalability reasons. Why exactly this was done is described in the section
"[Scale-Out the application](#scale-out-the-application)".
Before you use it in your application, you should check if you really need it.
This model adds risky complexity to the codebase. Also note that nothing I've done here is required to
apply the basics of the CQRS pattern. Look at
"[Busting some CQRS myths](https://lostechies.com/jimmybogard/2012/08/22/busting-some-cqrs-myths/)"
by Jimmy Bogard for further reading.

There's also a
[Process Manager](http://www.enterpriseintegrationpatterns.com/patterns/messaging/ProcessManager.html)
involved.
Its name is referee and it's a
[message handler](/src/ConnectFour/Port/Adapter/Messaging/RefereeMessageHandler.php).
The referee picks up a player joined event and ensures, that a chat is initiated.
When the chat is initiated, it assigns the chat to the game.
This is done, so the storage of games and chats can be on different MySQL instances.
This allows to scale the games and the chats independently, but ensures consistency with
[eventual consistency](https://en.wikipedia.org/wiki/Eventual_consistency).

I've chosen MySQL as the command side storage. Since the games are stored as json, MySQL is used as a document store.
On the query side, I've chosen Redis as the storage, since there are no relational queries to perform.
Note that on the command side you'll need a database that allows you to store the domain model as well as
the events in a single transaction. Another possibility is to use
[Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html)
as the storage model. With this model, only the events are saved.
The game aggregate is actually a good fit for the event sourcing approach.
Maybe I'll implement this storage model in the next game.

### Identity

The
[Identity](/src/Identity)
context is managing the user identities. To organize the business logic I've chosen the
[Domain Model](https://martinfowler.com/eaaCatalog/domainModel.html)
pattern backed up by an ORM. In this case I've chosen Doctrine because it's a really matured ORM that applies the
[Data Mapper](https://martinfowler.com/eaaCatalog/dataMapper.html)
pattern. The main responsibilities are that users can sign up, authenticate, change username and change password.

As the
[folder structure](/src/Identity)
shows, this context uses the "Ports and Adapters" architecture. The
[Application Layer](https://martinfowler.com/eaaCatalog/serviceLayer.html)
uses a traditional application service. Cross-cutting concerns are handled via a decorated
[mediator](/src/Common/Bus/Bus.php)
instead of handling them directly inside the service.

The public interface is formed by a
[controller](/src/Identity/Port/Adapter/Http/UserController.php),
which can be called up via http.

### Web Interface

The
[Web Interface](/src/WebInterface)
acts like an
[Backend For Frontend](https://samnewman.io/patterns/architectural/bff/).
All browser interactions go through this context. The main responsibilities are the session management
and the aggregation of the data from the other contexts. The
[JavaScript](/assets/js)
and
[StyleSheet](/assets/css)
are also defined here.

There're currently three pages.
1. The first page is the game lobby. Users can come together here to open or join games.
If John opens a game and Jane clicks on it, both have a game against each other.
If John clicks on his own game, the game will be aborted.
2. The second page is the game itself. The users play against each other and can write messages.
3. The third page is the user profile. Users can see a history of past games here.

## Transition to Microservices

This application matches all requirements to be a so called
[Microservice Architecture](https://martinfowler.com/articles/microservices.html),
except for deployment. This section describe the steps which needs to be done to fulfill the microservice requirement.
The true microservice approach isn't done, because I don't need it. I'm a single developer and it isn't worthwhile
here. The only requirement I've set myself is the high scalability which is described in the next section. However,
I've assigned an abstraction layer to the Web Interface context for easier migration to the microservice architecture.

To have single deployable units, the following steps needs to be done
1. Copy the folders (at
[config](/config),
[src](/src)
and
[tests](/tests))
in a separate application for each context or the context that's worthwhile to be a single deployable unit.
Because there are direct method invocations to the controllers (except WebInterface),
the routing needs to be defined.
2. The WebInterface is the only context which performs direct method invocations to the others.
This needs to be rewritten. The interfaces in the folder
[src/WebInterface/Application](/src/WebInterface/Application)
need new implementations which are currently located in
[src/WebInterface/Infrastructure/Integration](/src/WebInterface/Infrastructure/Integration).
They're need to make
[rpc](https://en.wikipedia.org/wiki/Remote_procedure_call)
calls.

__It's totally fine to invoke the application layer of the other contexts directly.
It helps with type safety and adds other benefits that you get from a monolithic approach.
I've added this layer of abstraction to write this section.__

## Scale-Out the application

Scale-Out is a technique where you can put as much servers in parallel as possible to handle high loads.
I've set this requirement for this application. This section describe how this requirement is fulfilled.

The application itself is stateless. This means that the application store lives in a different location.
We can scale the application when we put a
[Load Balancer](https://en.wikipedia.org/wiki/Load_balancing_(computing))
in front of it.

In the next step we've to scale the databases. We've to divide this into two parts
1. First we want to scale the databases for reading purposes.  
Since there should not be a concurrency problem in this application, we can add replicas for the MySQL and Redis stores.
2. Then we want to scale the databases for writing purposes.  
__Example for connect four__: This context already can be scaled-out by
[sharding](https://en.wikipedia.org/wiki/Shard_(database_architecture))
the database, since queries that span multiple games have been offloaded. How this is made possible is described
[in this section](#connect-four).
Only the game id is needed for the execution of the command model,
which is why it's well suited for the sharding key.
Sharding is done at the application level, more specifically in the
[repository](/src/ConnectFour/Port/Adapter/Persistence/Repository/DoctrineJsonGameRepository.php).
The application uses schema-based sharding and is aware of all existing logical shards,
while it's only aware of one physical connection. To actually forward queries to separate physical shards,
a proxy such as ProxySQL can be used. An example will be added with
[#118](https://github.com/marein/php-gaming-website/issues/118).  
__Example for chat__: Currently there shouldn't be queries that span multiple chats.
To invoke a chat operation (either writing or reading) we exclusively need a chat id.
As in connect four context, we can use
[sharding](https://en.wikipedia.org/wiki/Shard_(database_architecture))
for the chat context, where the chat id is the sharding key.

You may have seen that all contexts uses only one MySQL and one Redis instance.
This could be different for the production environment depending on the scale.
For this reason, different databases can be configured for the different contexts. Have a look at the
[configuration file](/.env).
We can split this even further.
For example, we can create a Redis instance per query model in the "Connect Four" context.
Of course, the code must be adapted. Whether it's worth it depends also on the scale.

## Chosen technologies

It's mainly written with PHP, but also JavaScript
for the frontend. I've chosen
[Symfony](https://symfony.com)
as the underlying framework, because I know it and I'm free to choose my application architecture,
or in this context, the directory structure.

Some other technologies:
* [MySQL](https://www.mysql.com) as the main storage of the contexts.
* [ProxySQL](https://proxysql.com) as a connection pool and for query routing / database sharding.
* [Redis](https://redis.io) for the query models or as a caching layer. Also the user sessions are stored here.
* [Rabbit Mq](https://www.rabbitmq.com) as the message broker.
* [Nchan](https://nchan.io) for real-time browser notifications.
* [Grafana](https://grafana.com) and [Prometheus](https://prometheus.io) for observability.
* Various [libraries](/composer.json) for php.

## A note on testing

The unit tests written in this application focuses on the business logic.
You can run them as follows.

```
./project unit
```

There are also acceptance tests (not many yet) that check if all is working and wired together as expected.
Acceptance tests work directly on the production images which gets pushed to docker hub.
You can run them as follows.

```
./project acceptance
```
