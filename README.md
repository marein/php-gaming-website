# php-gambling-website

__Table of contents__

* [Overview](#overview)
* [Installation and requirements](#installation-and-requirements)
* [Context is king](#context-is-king)
  * [Chat](#chat)
  * [Common](#common)
  * [Connect Four](#connect-four)
  * [User](#user)
  * [Web Interface](#web-interface)
* [Technologies](#technologies)
* [A note on testing](#a-note-on-testing)

## Overview

This is my playground project to explore new ideas and concepts. It's a gambling website where people can play against
each other. Currently, the only game is [Connect Four](#connect-four) but I plan other games to show more concepts.

To get a ready-to-start project, the code and the development environment are in one git repository.

The application is split into several parts
([Bounded Context](https://martinfowler.com/bliki/BoundedContext.html)).
None of them has hard dependencies (namely type dependencies) to each other.
I used modelling techniques like
[Domain Driven Design](http://domainlanguage.com/ddd/reference/)
and
[Event Storming](https://en.wikipedia.org/wiki/Event_storming)
to define them. Well, you only see the result and not the breakthroughs I went through.

Some people say "I need a showcase how to do Domain Driven Design, I don't get the concepts." or
"How to code with Domain Driven Design?". Firstly, Domain Driven Design isn't a technically approach. It's used to
refine your domain. Get the real insights and so on. So, Domain Driven Design isn't code but rather a modelling
technique. Technically patterns like repositories, aggregates or domain models have less to do with the concept.
It can be used in some contexts where it fits. But other contexts are better made simple with CRUD-ish implementations.
However, I also was at the point where I don't know how to start. And I asked the questions above myself too.
This project is my example showcase for the community. It's not perfect. Every day is a learning day.

As integration techniques I choose
[REST](https://martinfowler.com/articles/enterpriseREST.html),
messaging via a
[Message Broker](https://en.wikipedia.org/wiki/Message_broker)
or direct method invocation.
There're many other patterns, more of technically nature, I used in some contexts where it makes sense.
They're defined in the section which describes the respective context, but I presuppose you've read
[Implementing Domain Driven Design](https://vaughnvernon.co/?page_id=168#iddd)
by Vaughn Vernon and
[Tackling Complexity in the Heart of Software](http://dddcommunity.org/book/evans_2003/)
by Eric Evans.

This application is built with a
[Microservice Architecture](https://martinfowler.com/articles/microservices.html) and scale out techniques
in mind. Some parts of the application talks via messaging. All synchronous calls are
done via method invocation. I know, method invocation isn't an inter-service call (for example with http),
so the parts of the application must be deployed together, but there're very few steps to reach
the fully microservice approach. The first step is to split out the folders (at
[config](/code/app/config)
and
[src](/code/src))
in a separate application for each context. After that, the
[Web Interface](#web-interface)
needs a short rewrite as described in its section.

You may recognize that there is only one mysql and one redis instance. But when you look at the
[Environment File](/container/environment.env), you'll see that you can define different servers for each context.
So, each part scales nicely.

## Installation and requirements

To install the project with the built in script you need
[Docker](https://www.docker.com),
[Docker Compose](https://docs.docker.com/compose/),
and an unix-like shell, since the build script use the "rm -rf" command.
Surely you can setup a server yourself.

```
git clone https://github.com/marein/php-gambling-website
cd php-gambling-website
./project build
```

__Note__  
If you update your local repository, you have to "./project build" again.
I don't write any migration scripts on schema changes. Especially for the Connect Four context.
This project is in development and I don't pollute the code with schema changes at this stage.

There're several other commands in the project script. You see them with

```
./project help
```

Since I use the latest JavaScript techniques, like EcmaScript 6, it does not work in all browsers.
Currently, it's only tested with Safari. And also don't look at the frontend design. This is by all means not my domain.

## Context is king

As stated above, the application is split into different contexts. Each with its own responsibilities and techniques.
They're defined below. Because I want a showcase with many different techniques,
they're all rely on different patterns.

### Chat

The
[Chat](/code/src/Chat)
is a really simple one. There's only one business rule. That's why I used a
[Transaction Script](https://martinfowler.com/eaaCatalog/transactionScript.html)
to implement the business logic. There's no domain model or something. The simplicity of this context is also
not worth the layering chosen by the other contexts. So, everything is just thrown in the root folder.

Other services can interact with this service through the
[controller](/code/src/Chat/Http/ChatController.php)
for query and some command calls, or through the message broker for command calls.
The message broker adapter is defined in a
[console task](/code/src/Chat/Console/RabbitMqCommandListenerCommand.php).

The published
[Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
gets persisted to the event store. A
[console task](/code/src/Chat/Console/PublishStoredEventsToRabbitMqCommand.php)
pick up this events and publish them to the message broker so other contexts are informed about whats happened.

__Short description of what's going on here__  
A chat can be initiated, and its messages can be shown. If a chat has explicitly authors assigned, only those authors
are allowed to access the chat. Messages are cut to 140 characters.

### Common

The
[Common](/code/src/Common)
folder provides reusable components. If the project is more advanced,
I may split them out. But there're already battle tested implementations out there (like a
[Bus](https://tactician.thephpleague.com) by Tactician,
or an
[Event Store](https://github.com/prooph/event-store) by prooph).
You may use them, instead of mine. The
[Event Store](/code/src/Common/EventStore)
implementation inside
[Common](/code/src/Common)
isn't used to be a fully store for an
[Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html)
model. It's really just a store for events.

### Connect Four

The
[Connect Four](/code/src/ConnectFour)
is the "heart of the software". Its logic is definitely worth to build a proper
[Domain Model](https://martinfowler.com/eaaCatalog/domainModel.html).

I also use MySQL as a document storage, and store the games as json.

As the folder structure reveals, this context uses the "Ports and Adapters" architecture. The
[Application Layer](https://martinfowler.com/eaaCatalog/serviceLayer.html)
uses a command and query bus. The opposite of this approach is the traditional application service I used in the
[User](#user)
context. It boils down to one class with many methods vs. many classes with one method.

Because the domain model is very complex, I don't want to leak its internals to the
[Adapter Layer](/code/src/ConnectFour/Port/Adapter). That's why I used
[CQRS](https://martinfowler.com/bliki/CQRS.html)
to split the model into the read and into the write side. This isn't only done for the classes in code,
but also at the storage layer. The game at the command side is stored to MySQL. The game and all its
query methods at the read side is stored to Redis. When a game at the command side is stored, the published
[Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
are also stored in the same transaction to MySQL. A little bit later a
[console task](/code/src/ConnectFour/Port/Adapter/Console/BuildQueryModelCommand.php)
pick up these events and build the query models. This way, the command side can scale independently from the read side.
The game is usually a hot aggregate. Maybe later we have to scale out and add more MySQL instances to handle the load at
the write side. The sharding of the games is really straightforward, since we can use the uuids.
"SELECT COUNT(id) FROM game WHERE status = 'open';" does not work anymore, because the games are in many
different servers. The idea is, that the query model builder pick the domain events
from all MySQL instances and create the open games in Redis. That's the concept of
[eventual consistency](https://en.wikipedia.org/wiki/Eventual_consistency).
A disadvantage from this approach is, that the data isn't immediately consistent, but the domain allows it. The
browser didn't miss a player move, because nchan (the pubsub server I use in this project) hold every event for
10 seconds in its buffer, and publish this events to the client which connects to the system. The query builder has
enough time to compensate the eventually consistent lack.
This model adds risky complexity to the codebase. Be sure you need it, before you use it in your own projects.

There's also a
[Process Manager](http://www.enterpriseintegrationpatterns.com/patterns/messaging/ProcessManager.html)
involved as a
[console task](/code/src/ConnectFour/Port/Adapter/Console/RefereeCommand.php).
Its name is referee and it picks up a player joined event and ensures, that a chat is initiated.
When the chat is initiated it assigns the chat to the game. This is done, so the storage of games and chats
can be on different MySQL instances. This allows, to scale the games and the chats independently, but ensures
consistency with
[eventual consistency](https://en.wikipedia.org/wiki/Eventual_consistency).

The published
[Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
gets persisted to the event store. A
[console task](/code/src/ConnectFour/Port/Adapter/Console/PublishStoredEventsToRabbitMqCommand.php)
pick up this events and publish them to the message broker so other contexts are informed about whats happened.

Everything within the game aggregate, except the game itself, is a
[Value Object](https://martinfowler.com/bliki/ValueObject.html).
I like the concept of immutability. It helps a lot to write robust code.

__Short description of what's going on here__  
The player can open a game and decide which winning rules (horizontal, vertical, diagonal)
and which board size he wants (this is technically possible, but the ui doesn't ask for this at the moment).
The game remains open until another player joins the party. The game can be aborted until the second move is done. After
the second move, a player can resign the game (currently not implemented). The game gets played until there's a draw,
a winner or a player resigns. When a player joins the party, the referee initiate a chat, so the players can talk
to each other.

### User

The
[User](/code/src/User)
context is currently just bootstrapped. Nothing is interacting with this service.

As the folder structure reveals, this context uses the "Ports and Adapters" architecture. It also uses
[Doctrine](http://www.doctrine-project.org)
to throw an OR-Mapper in the mix. I also used to create a traditional
[Application Service](https://martinfowler.com/eaaCatalog/serviceLayer.html).
For example, all user interactions are defined in the
[user application service](/code/src/User/Application/User/UserService.php).
The opposite of this approach is the command and query bus I used in the
[Connect Four](#connect-four)
context. It boils down to one class with many methods vs. many classes with one method. The domain model will also
float around to the [Adapter Layer](/code/src/User/Port/Adapter). Mapping forth and back isn't worth in this context.

__Short description of what's going on here__  
A user can sign up with an username and a password. The user can also change its username and password.

### Web Interface

The
[Web Interface](/code/src/WebInterface)
acts like an
[Api Gateway](http://microservices.io/patterns/apigateway.html).
All browser interactions go through this context, because its main responsibility is the session management
and the aggregation of the data from the other contexts. The
[JavaScript](/code/src/WebInterface/Presentation/Http/JavaScript)
and
[StyleSheet](/code/src/WebInterface/Presentation/Http/StyleSheet)
are also defined here.

This is the only context which does direct method calls to
the others. But that's strictly defined too, because it uses only the controllers defined in the other contexts. These
controllers already return a json response. Surely, for the current implementation this abstraction is an overkill,
but as stated above, it helps to easily transition to a microservice approach. I don't recommend this
layer of abstraction if you don't need it in your project. It's totally fine to invoke the
[Application Layer](https://martinfowler.com/eaaCatalog/serviceLayer.html)
of the other contexts directly. It helps with type safety and adds other advantages you get from a monolithic approach.
The classes which must be changed/added to make http calls to the other context live under
[code/src/WebInterface/Infrastructure/Integration](/code/src/WebInterface/Infrastructure/Integration)
and must implement the interfaces at
[code/src/WebInterface/Application](/code/src/WebInterface/Application).

__Short description of what's going on here__  
There's the lobby, where users can open a game. When the user clicks at his own game, it will be aborted.
When another user clicks his game, the user joins and the two users have a match. In the game they can
play against each other and can send chat messages. All users are anonymous until they sign up with a real
username (not implemented yet). The profile shows the non aborted games of the current user.

## Technologies

It's mainly written with PHP, but also JavaScript
for the frontend. I choose
[Symfony](https://symfony.com)
as the underlying framework, because I know it and it let me freely choose my application architecture,
or in this context, the directory structure.

Some other technologies:
* [MySQL](https://www.mysql.com) as the main storage of the contexts.
* [Redis](https://redis.io) for the query models or as a caching layer. Also the user sessions are stored here.
* [Rabbit Mq](https://www.rabbitmq.com) as the message broker.
* [Nchan](https://nchan.io) for real-time browser notifications.
* [Makefile](/code/Makefile) for concatenation of javascript and css.

## A note on testing

There're currently no tests written. Maybe this comes later on the road. The game aggregate of the connect four context
is a port from another project of mine. You can find it
[here](https://github.com/marein/php-connect-four).
It's connect four for the command line. This project is tested, but not so complex like the game at the connect four
context.
