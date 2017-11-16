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
and Scale-Out Techniques in mind.
The sections
[Content is king](#context-is-king),
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
and to write messages in the chat. The layering chosen in the other contexts isn't worthwhile here.

The public interface is formed by a
[controller](/code/src/Chat/Http/ChatController.php),
which can be called up via http, and a
[command line task](/code/src/Chat/Console/RabbitMqCommandListenerCommand.php),
which serves as an interface to a message broker.
Other contexts 

This context publishes
[Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
through the message broker to inform other contexts what's happened here.

### Common

The
[Common](/code/src/Common)
folder provide reusable components. If the project is more advanced, I'll outsource them as libraries.
But there're already battle tested implementations out there (like a
[Bus](https://tactician.thephpleague.com) by Tactician,
or an
[Event Store](https://github.com/prooph/event-store) by prooph).
You may use them, instead of mine. The
[Event Store](/code/src/Common/EventStore)
implementation inside
[Common](/code/src/Common)
isn't used to be a storage for an
[Event Sourcing](https://martinfowler.com/eaaDev/EventSourcing.html)
model. It's really just a store for events.

### Connect Four

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

There're currently two pages.

The first page is the game lobby. Users can come together here to open or join games.
If John opens a game and Jane clicks on it, both have a game against each other.
If John clicks on his own game, the game will be aborted.

The second page is the user profile. Users can see a history of past games here.

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
