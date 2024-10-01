# php-gaming-website

__Table of contents__

* [Overview](#overview)
* [Deploy the app](#deploy-the-app)
* [Context is king](#context-is-king)

## Overview

This is a web-based board game platform designed for players to connect and play.
Alongside the gaming experience, it showcases a range of software engineering concepts, including a modular,
[reactive](https://www.reactivemanifesto.org), [domain-driven](https://en.wikipedia.org/wiki/Domain-driven_design)
backend architecture that ensures scalability, real-time browser notifications,
and observability - while using technologies often underestimated for their capabilities.

**Curious about how it all works?** Take a deeper dive into the system design in [Context is king](#context-is-king).

**Ready to set it up?** Keep on reading to find out how to [deploy the app](#deploy-the-app) in different environments.

## Deploy the app

The most convenient way to deploy the application is to use [Docker](https://www.docker.com/),
either with the [Docker Compose](https://docs.docker.com/compose/) plugin
or [Docker Swarm](https://docs.docker.com/engine/swarm/).
Choose a deployment environment below and follow the guide to get the app up and running.

<details>
  <summary>Deploy for local development</summary>

  ### Deploy for local development

  Clone the repository and execute `./project build` to run the application. This command uses
  [Docker Compose](https://docs.docker.com/compose/) and copies downloaded dependencies from
  the container to the host system, enabling autocompletion.

  Once the project is up and running, the following URLs will be accessible:

  | URL                                              | Information                    |
  |--------------------------------------------------|--------------------------------|
  | [http://localhost/](http://localhost/)           | The application.               |
  | [http://localhost:8081/](http://localhost:8081/) | MySQL management interface.    |
  | [http://localhost:8082/](http://localhost:8082/) | Redis management interface.    |
  | [http://localhost:8083/](http://localhost:8083/) | Grafana management interface.  |

  Use `./project tests` to ensure code quality and consistency. This command performs code style checks,
  runs static analysis and executes the test suite to verify the application's functionality.
  It is also integrated into the pipeline to automate these checks upon pushing code.

  Use `./project composer` to manage dependencies and `./project installAssets` to install web assets
  during development. Both commands copy dependencies from the container to the host system upon completion,
  enabling autocompletion.

  > Additional commands helpful during development can be found by running `./project help`.

  > Updating the codebase will automatically restart long-running processes,
  > such as queue consumers, ensuring that changes are applied immediately.

  > After pulling updates from the repository, remember to run ./project build again.
  > Since the app isn't deployed to a production server, schema changes are consolidated to keep the codebase clean.
</details>

<details>
  <summary>Deploy prod on a single server</summary>

  ### Deploy prod on a single server

  Clone the repository or download [this file](/deploy/single-server/docker-compose.yml),
  and execute `docker compose -f deploy/single-server/docker-compose.yml up -d` or
  `docker stack deploy -c deploy/single-server/docker-compose.yml app`
  to deploy the application in a production environment.

  Alternatively, [click here](http://play-with-docker.com?stack=https://raw.githubusercontent.com/marein/php-gaming-website/master/deploy/single-server/docker-compose.yml)
  to deploy the application on [Play with Docker](http://play-with-docker.com).
</details>

<details>
  <summary>Deploy prod for load testing</summary>

  ### Deploy prod for load testing

  This is not merged yet, but feel free to have a look at [#170](https://github.com/marein/php-gaming-website/pull/170).
</details>

## Context is king

The platform features a modular, [reactive](https://www.reactivemanifesto.org),
[domain-driven](https://en.wikipedia.org/wiki/Domain-driven_design) backend architecture. Each
[context](https://martinfowler.com/bliki/BoundedContext.html) ships as a [module](/src) or
[service](https://github.com/gaming-platform?q=service-) that scales independently by defining its own
resources, such as databases, and communicates via messaging to reduce temporal coupling.

Check out the purpose and architectural decisions of each context in the sections below.

<details>
  <summary>Chat</summary>

  ### Chat

  **Purpose**: [Chat](/src/Chat) allows other contexts, like Connect Four, to initiate chats. Authors can list and write messages
  in these chats based on their access rights.

  **Communication**: Its use cases are exposed via
  [messaging](https://www.enterpriseintegrationpatterns.com/patterns/messaging/Messaging.html), utilizing
  [request-reply](https://www.enterpriseintegrationpatterns.com/patterns/messaging/RequestReply.html),
  with some directly invoked by the Web Interface to reduce network hops and abstractions.
  To notify other contexts about what has happened, [Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
  are stored in a [transactional outbox](https://en.wikipedia.org/wiki/Inbox_and_outbox_pattern) and
  later published in [Protobuf](https://en.wikipedia.org/wiki/Protocol_Buffers) format using
  [publish-subscribe](https://www.enterpriseintegrationpatterns.com/patterns/messaging/PublishSubscribeChannel.html).
  A list of available messages [can be found here](https://github.com/gaming-platform/api).

  **Architecture**: Internally, it uses
  [ports and adapters](https://en.wikipedia.org/wiki/Hexagonal_architecture_(software)) to separate business logic
  from external systems. A [mediator](https://en.wikipedia.org/wiki/Mediator_pattern) exposes the
  [application layer](https://martinfowler.com/eaaCatalog/serviceLayer.html), routing requests to handlers
  and handling cross-cutting concerns like validation and transaction management. Business logic is organized using a
  [transaction script](https://martinfowler.com/eaaCatalog/transactionScript.html).

  **Infrastructure**: MySQL is used to store chats, messages and events (outbox), while Redis enables
  [idempotent messaging](https://www.enterpriseintegrationpatterns.com/patterns/messaging/IdempotentReceiver.html)
  to ensure that each message is processed exactly once, and RabbitMQ facilitates communication with other contexts.

  **Scalability**: The module is stateless, enabling it to scale horizontally by adding more instances.
  Current usage patterns of MySQL don’t require sharding, but chat IDs would be well-suited for partitioning if needed.
</details>

<details>
  <summary>Connect Four</summary>

  ### Connect Four
</details>

<details>
  <summary>Identity</summary>

  ### Identity
</details>

<details>
  <summary>Web Interface</summary>

  ### Web Interface

  **Purpose**: [Web Interface](/src/WebInterface) ties all modules together and serves as the main point of
  interaction for users.

  **Communication**: It directly invokes use cases from other modules to reduce network hops and abstractions,
  and calls other services via [request-response](https://en.wikipedia.org/wiki/Request–response). To notify users
  in real-time about what has happened, it subscribes to events from other contexts, using
  [publish-subscribe](https://www.enterpriseintegrationpatterns.com/patterns/messaging/PublishSubscribeChannel.html),
  and forwards them to subscribed users.

  **Architecture**: Internally, it uses a form of
  [layered architecture](https://en.wikipedia.org/wiki/Multitier_architecture).

  **Infrastructure**: Session storage is managed through Redis, RabbitMQ facilitates communication with other contexts,
  and Nchan notifies users in real-time.

  **Scalability**: The module is stateless, enabling it to scale horizontally by adding more instances.
  Some queues can be sharded using RabbitMQ's
  [consistent hash exchange](https://github.com/rabbitmq/rabbitmq-server/blob/main/deps/rabbitmq_consistent_hash_exchange/README.md)
  to distribute the load across multiple CPUs. Nchan performs well under current usage patterns, maintaining
  low latency and responsiveness even under high load.

  **Alternatives**: Instead of organizing the Web Interface horizontally, it could be embedded within the verticals
  to achieve higher [cohesion](https://en.wikipedia.org/wiki/Cohesion_(computer_science)).
  [UI composition](https://www.jimmybogard.com/composite-uis-for-microservices-a-primer/) would be done using
  [ESI](https://en.wikipedia.org/wiki/Edge_Side_Includes)/[SSI](https://en.wikipedia.org/wiki/Server_Side_Includes)
  to aggregate fragments from each context.
</details>

> [src/Common](/src/Common) contains supporting libraries that may be moved to separate repositories in the future,
> see [#35](https://github.com/marein/php-gaming-website/issues/35).
