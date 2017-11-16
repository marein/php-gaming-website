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
* [Transition to Microservices](#transition-to-microservices)
* [Scale-Out the application](#scale-out-the-application)
* [Chosen technologies](#chosen-technologies)
* [A note on testing](#a-note-on-testing)

## Overview

This is my playground project to explore new ideas and concepts. It's a gambling website where people can play against
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
describe whats done to apply these concepts.

To get a ready-to-start project, the code and the development environment are in one git repository.

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
This project is in development and I don't pollute the code with schema changes at this stage.

There're several other commands in the project script. You see them with

```
./project help
```

Since I use the latest JavaScript techniques, like EcmaScript 6, it doesn't work in all browsers.
Currently, it's only tested with Safari.
Don't look at the front end design, this is by no means my domain.

## Context is king

The application is split into several
[Bounded Contexts](https://martinfowler.com/bliki/BoundedContext.html).
I've chosen modeling techniques like
[Domain Driven Design](http://domainlanguage.com/ddd/reference/)
and
[Event Storming](https://en.wikipedia.org/wiki/Event_storming)
to define them. Well, you only see the result and not the breakthroughs I went through.
The following sections describe what techniques I use in the respective context.

I presuppose you've read
[Implementing Domain Driven Design](https://vaughnvernon.co/?page_id=168#iddd)
by Vaughn Vernon and
[Tackling Complexity in the Heart of Software](http://dddcommunity.org/book/evans_2003/)
by Eric Evans.

### Chat

This context is very simple. To organize the business logic, the
[Chat](/code/src/Chat)
uses the 
[Transaction Script](https://martinfowler.com/eaaCatalog/transactionScript.html)
pattern. Its only job is to initiate chats, list messages by chat
and to write messages in the chat. If authors are assigned to a chat, only those authors can write and read messages.
The layering chosen in the other contexts isn't worthwhile here.

The public interface is formed by a
[controller](/code/src/Chat/Http/ChatController.php),
which can be called up via http, and a
[command line task](/code/src/Chat/Console/RabbitMqCommandListenerCommand.php),
which serves as an interface to a message broker.

This context publishes
[Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
through the message broker to inform other contexts what's happened here.
First the domain events are stored to the event store.
This happens in the same transaction in which the commands are executed.
After that, a
[command line task](/code/src/Chat/Console/PublishStoredEventsToRabbitMqCommand.php)
publish these stored events to the message broker.

I've chosen MySQL as the storage.

### Common

The
[Common](/code/src/Common)
folder provide reusable components. If the project is more advanced, I'll outsource them as libraries.
But there're already battle tested implementations out there (like a
[Bus](https://tactician.thephpleague.com)
by Tactician, or an
[Event Store](https://github.com/prooph/event-store)
by prooph). You may use them, instead of mine. The
[Event Store](/code/src/Common/EventStore)
implementation inside
[Common](/code/src/Common)
isn't used to be a storage for an
[Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html)
model. It's really just a store for events.

### Connect Four

The
[Connect Four](/code/src/ConnectFour)
is the context where I put the most effort in. The business logic is definitely worth building a proper
[Domain Model](https://martinfowler.com/eaaCatalog/domainModel.html). Players can open, join, abort and resign a game.
Of course they can also perform moves. The game can be aborted until the second move.
After the second move, players can only resign or finish the game. The referee, which sits near the game desks,
ensure that the people can talk to each other. This process is described below.

As the
[folder structure](/code/src/ConnectFour)
shows, this context uses the "Ports and Adapters" architecture. The
[Application Layer](https://martinfowler.com/eaaCatalog/serviceLayer.html)
uses a command and query bus. The opposite of this approach is the traditional application service I use in the
[User](#user)
context. It boils down to one class with many methods vs. many classes with one method.

The public interface is formed by a
[controller](/code/src/ConnectFour/Port/Adapter/Http/GameController.php),
which can be called up via http.

This context publishes
[Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
through the message broker to inform other contexts what's happened here.
First the domain events are stored to the event store.
This happens in the same transaction in which the commands are executed.
After that, a
[command line task](/code/src/ConnectFour/Port/Adapter/Console/PublishStoredEventsToRabbitMqCommand.php)
publish these stored events to the message broker.

The Connect Four context applies the
[CQRS](https://martinfowler.com/bliki/CQRS.html)
pattern. Not only the domain model is divided into command and query side, but also the storage layer.
The query model is stored in an
[eventual consistency](https://en.wikipedia.org/wiki/Eventual_consistency)
manner. A
[command line task](/code/src/ConnectFour/Port/Adapter/Console/BuildQueryModelCommand.php)
retrieves the stored events from the event store and then builds the query model.
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
[command line task](/code/src/ConnectFour/Port/Adapter/Console/RefereeCommand.php).
The referee picks up a player joined event and ensures, that a chat is initiated.
When the chat is initiated, it assigns the chat to the game.
This is done, so the storage of games and chats can be on different MySQL instances.
This allows to scale the games and the chats independently, but ensures consistency with
[eventual consistency](https://en.wikipedia.org/wiki/Eventual_consistency).

I've chosen MySQL as the command side storage. Since the games are stored as json, MySQL is used as a document store.
On the query side, I've chosen Redis as the storage, since there are no relational queries to perform.
Of course, I could've used Redis for the command side, but I'm more familiar with MySQL.
Note that you need to have a database that allows you to store the domain model as well as
the events in a single transaction. Another possibility is to use
[Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html)
as the storage model. With this model, only the events are saved.

### User

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

There're currently three pages.

The first page is the game lobby. Users can come together here to open or join games.
If John opens a game and Jane clicks on it, both have a game against each other.
If John clicks on his own game, the game will be aborted.

The second page is the game itself. The users play against each other and can write messages.

The third page is the user profile. Users can see a history of past games here.

## Transition to Microservices

## Scale-Out the application

## Chosen technologies

It's mainly written with PHP, but also JavaScript
for the frontend. I've chosen
[Symfony](https://symfony.com)
as the underlying framework, because I know it and I'm free to choose my application architecture,
or in this context, the directory structure.

Some other technologies:
* [MySQL](https://www.mysql.com) as the main storage of the contexts.
* [Redis](https://redis.io) for the query models or as a caching layer. Also the user sessions are stored here.
* [Rabbit Mq](https://www.rabbitmq.com) as the message broker.
* [Nchan](https://nchan.io) for real-time browser notifications.
* [Makefile](/code/Makefile) for concatenation of javascript and css.

## A note on testing

There're currently no written tests. Maybe this comes later on the road. The game aggregate of the connect four context
is a port from another project of mine. You can find it
[here](https://github.com/marein/php-connect-four).
It's connect four for the command line. This project is tested, but not as complex as the game at the connect four
context.
