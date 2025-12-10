# php-gaming-website

__Table of contents__

* [Overview](#overview): A brief introduction to the platform.
* [Deployment Guide](#deployment-guide): Instructions for deploying the application in different environments.
* [System Design](#system-design): Details about the functionality and architectural decisions.
* [Technology Stack](#technology-stack): Summary of the technologies used across the platform.

## Overview

This is a web-based board game platform designed for players to connect and play.
Alongside the gaming experience, it showcases a range of software engineering concepts, including a modular,
[reactive](https://www.reactivemanifesto.org), [domain-driven](https://en.wikipedia.org/wiki/Domain-driven_design)
backend architecture that ensures scalability, real-time browser notifications, and observability.

Refer to the [System Design](#system-design) for functionality and architectural details, the
[Deployment Guide](#deployment-guide) for setup instructions, and the [Technology Stack](#technology-stack)
for information on the technologies used.

## Deployment Guide

To deploy the application, it is recommended to use [Docker](https://www.docker.com/)
with either the [Docker Compose](https://docs.docker.com/compose/) plugin
or [Docker Swarm](https://docs.docker.com/engine/swarm/).

Choose a deployment environment below and follow the guide to get the application up and running.

<details>
  <summary>For Local Development</summary>

  ### For Local Development

  To deploy the application for local development, clone the repository and run `./project build`. This
  command uses [Docker Compose](https://docs.docker.com/compose/) and copies downloaded dependencies from
  the container to the host system, enabling autocompletion.

  Once the project is up and running, the following URLs will be accessible:

  | URL                                              | Information                    |
  |--------------------------------------------------|--------------------------------|
  | [http://localhost/](http://localhost/)           | The application.               |
  | [http://localhost:8081/](http://localhost:8081/) | MySQL management interface.    |
  | [http://localhost:8082/](http://localhost:8082/) | Redis management interface.    |
  | [http://localhost:8083/](http://localhost:8083/) | Grafana management interface.  |

  Run `./project tests` to verify code quality and functionality. This command performs code style checks,
  runs static analysis, and executes the test suite. Automated checks are integrated into the pipeline and
  executed upon code submission.

  Use `./project composer` to manage dependencies and `./project installAssets` to install web assets
  during development. Both commands copy dependencies from the container to the host system upon completion,
  enabling autocompletion.

  > Additional commands helpful during development can be found by running `./project help`.

  > Updating the codebase will automatically restart long-running processes,
  > such as queue consumers, ensuring that changes are applied immediately.

  > After pulling updates from the repository, re-run `./project build` to incorporate the latest changes.
  > Schema changes are consolidated to maintain a clean codebase.

  > Infrastructure components are shared across contexts to reduce resource usage and configuration complexity.
  > For a more sophisticated setup, take a look at the deployment "Production for Load Testing".
</details>

<details>
  <summary>Production on Single Server</summary>

  ### Production on Single Server

  To deploy the application in a production environment, either clone the repository or
  [download the deployment file](/deploy/single-server/docker-compose.yml). Then, run
  `docker compose -f deploy/single-server/docker-compose.yml up -d` or
  `docker stack deploy -c deploy/single-server/docker-compose.yml app`.

  Alternatively, [click here](http://play-with-docker.com?stack=https://raw.githubusercontent.com/marein/php-gaming-website/master/deploy/single-server/docker-compose.yml)
  to deploy the application on [Play with Docker](http://play-with-docker.com).

  > Infrastructure components are shared across contexts to reduce resource usage and configuration complexity.
  > For a more sophisticated setup, take a look at the deployment "Production for Load Testing".
</details>

<details>
  <summary>Production for Load Testing</summary>

  ### Production for Load Testing

  This is the most sophisticated deployment designed for evaluating the platform’s performance and scalability
  under extreme load. Leveraging [Docker Swarm](https://docs.docker.com/engine/swarm/), this configuration
  enables scaling across multiple nodes, making it ideal for stress testing and pinpointing bottlenecks.
  The stack defines 5 physical MySQL shards for Connect Four, as this context receives the highest load.

  Before deploying the application, ensure that the Swarm nodes are labeled correctly to distribute services as needed.

  <details>
    <summary>Example Node Setup</summary>

  | Node       | Labels                                                    |
  |------------|-----------------------------------------------------------|
  | manager-01 | `traefik=1` `nchan=1` `grafana=1` `prometheus=1`          |
  | node-01    | `chat-mysql=1` `identity-mysql=1` `web-interface-redis=1` |
  | node-02    | `rabbit-mq=1`                                             |
  | node-03    | `connect-four-mysql-1=1` `connect-four-mysql-2=1`         |
  | node-04    | `connect-four-mysql-3=1` `connect-four-mysql-4=1`         |
  | node-05    | `connect-four-mysql-5=1` `connect-four-redis=1`           |
  | node-06    | `long-running=1` `needs-proxysql-sidecar=1`               |
  | node-07    | `long-running=1` `needs-proxysql-sidecar=1`               |
  | node-08    | `web-interface-http=1` `needs-proxysql-sidecar=1`         |
  | node-09    | `web-interface-http=1` `needs-proxysql-sidecar=1`         |
  | node-10    | `web-interface-http=1` `needs-proxysql-sidecar=1`         |
  | node-11    | `web-interface-http=1` `needs-proxysql-sidecar=1`         |
  | node-12    | `web-interface-http=1` `needs-proxysql-sidecar=1`         |
  | node-13    | `web-interface-http=1` `needs-proxysql-sidecar=1`         |
  </details>

  To deploy, clone the repository or download the [load-test](/deploy/load-test) directory, and switch to it on the Swarm manager.
  The stack combines multiple Compose files, deployable with a single command using:

  ```bash
  find stack \
    -name '*.yml' \
    -type f \
    -printf '-c %p ' \
    | xargs -I {} sh -c 'docker stack deploy {} --prune app'
  ```

  After the stack is successfully deployed, tweak `BASE_URL` and `vus` in the following command,
  then run it to start the load test:

  ```bash
  docker run --rm -i --network=host -e BASE_URL=https://127.0.0.1 grafana/k6:1.4.0 run \
    --vus 500 \
    --duration 1m \
    - < scenario/play-connect-four.js
  ```

  > This is not a trivial test. It involves all components, especially those handling gameplay. The scenario
  > simulates players continuously playing Connect Four, designed to stress the system and identify bottlenecks.

  > Grafana is accessible on port 8083, providing valuable insights into how each component performs.

  The following performance figures were measured using a deployment on nodes from the "Example Node Setup" with
  32 CPU cores each.

  <details>
    <summary>~27k req/s sustained for 1 minute with 500 vus</summary>

    ```
    data_received..................: 484 MB  8.0 MB/s
    data_sent......................: 488 MB  8.1 MB/s
    http_req_blocked...............: avg=4.25µs   min=390ns    med=1.42µs   max=62.13ms  p(90)=2.13µs   p(95)=2.57µs
    http_req_connecting............: avg=2.49µs   min=0s       med=0s       max=61.75ms  p(90)=0s       p(95)=0s
    http_req_duration..............: avg=18.11ms  min=5.86ms   med=16.61ms  max=286.72ms p(90)=23.33ms  p(95)=26.18ms
      { expected_response:true }...: avg=18.11ms  min=5.86ms   med=16.61ms  max=286.72ms p(90)=23.33ms  p(95)=26.18ms
    http_req_failed................: 0.00%   ✓ 0            ✗ 1646808
    http_req_receiving.............: avg=180.64µs min=10.08µs  med=36.94µs  max=66.97ms  p(90)=158.12µs p(95)=431.03µs
    http_req_sending...............: avg=11.91µs  min=3.54µs   med=9.52µs   max=118.65ms p(90)=13.93µs  p(95)=19.09µs
    http_req_tls_handshaking.......: avg=0s       min=0s       med=0s       max=0s       p(90)=0s       p(95)=0s
    http_req_waiting...............: avg=17.92ms  min=5.78ms   med=16.47ms  max=286.67ms p(90)=23.04ms  p(95)=25.77ms
    http_reqs......................: 1646808 27302.991135/s
    iteration_duration.............: avg=437.8ms  min=261.44ms med=425.99ms max=1.02s    p(90)=511.36ms p(95)=531.54ms
    iterations.....................: 68617   1137.624631/s
    vus............................: 500     min=500        max=500
    vus_max........................: 500     min=500        max=500
    ```
  </details>

  <details>
    <summary>~28k req/s sustained for 1 minute with 700 vus</summary>

    ```
    data_received..................: 512 MB  8.5 MB/s
    data_sent......................: 516 MB  8.5 MB/s
    http_req_blocked...............: avg=12.57µs  min=378ns    med=1.46µs   max=127.26ms p(90)=2.16µs   p(95)=2.62µs
    http_req_connecting............: avg=10.57µs  min=0s       med=0s       max=126.82ms p(90)=0s       p(95)=0s
    http_req_duration..............: avg=24.03ms  min=5.94ms   med=21.32ms  max=239.03ms p(90)=34.19ms  p(95)=40.53ms
      { expected_response:true }...: avg=24.03ms  min=5.94ms   med=21.32ms  max=239.03ms p(90)=34.19ms  p(95)=40.53ms
    http_req_failed................: 0.00%   ✓ 0            ✗ 1740456
    http_req_receiving.............: avg=218.48µs min=10.95µs  med=36.76µs  max=106.69ms p(90)=158.13µs p(95)=411.29µs
    http_req_sending...............: avg=12.7µs   min=3.54µs   med=9.76µs   max=120.08ms p(90)=14.14µs  p(95)=19.95µs
    http_req_tls_handshaking.......: avg=0s       min=0s       med=0s       max=0s       p(90)=0s       p(95)=0s
    http_req_waiting...............: avg=23.8ms   min=5.89ms   med=21.17ms  max=238.99ms p(90)=33.83ms  p(95)=39.99ms
    http_reqs......................: 1740456 28825.229391/s
    iteration_duration.............: avg=580.29ms min=339.31ms med=572.94ms max=888.56ms p(90)=663.75ms p(95)=688.81ms
    iterations.....................: 72519   1201.051225/s
    vus............................: 700     min=700        max=700
    vus_max........................: 700     min=700        max=700
    ```
  </details>

  The highest load achieved was over `50k req/s` sustained for `10 minutes`, maintaining a snappy UI, no errors, and
  ensuring transactional integrity without losing any messages. With more resources distributed across additional nodes,
  the `p95` latency can be kept consistently low, and messages flow through the system to the browser in real-time.
  Achieving this requires fine-tuning factors like the number of message consumers, the sharding of RabbitMQ queues
  and MySQL databases, and the distribution of Swarm services.
</details>

> All environments are served via HTTPS using self-signed certificates.
> For production deployments, it is recommended to use valid certificates from a trusted authority.

## System Design

The platform features a modular, [reactive](https://www.reactivemanifesto.org),
[domain-driven](https://en.wikipedia.org/wiki/Domain-driven_design) backend architecture. Each
[context](https://martinfowler.com/bliki/BoundedContext.html) ships as a [module](/src) or
[service](https://github.com/gaming-platform?q=service-) that scales independently by defining its own
resources, such as databases, and communicates via messaging to reduce temporal coupling.

Check out the purpose and architectural decisions of each context in the sections below.

<details>
  <summary>Chat</summary>

  ### Chat

  **Purpose**: [Chat](/src/Chat) enables other contexts, like Connect Four, to initiate chats.
  Authors can list and write messages in these chats based on their access rights.

  **Communication**: Its use cases are exposed via
  [messaging](https://www.enterpriseintegrationpatterns.com/patterns/messaging/Messaging.html), utilizing
  [Request-Reply](https://www.enterpriseintegrationpatterns.com/patterns/messaging/RequestReply.html),
  with some directly invoked by the Web Interface to reduce network hops and abstractions.
  To notify other contexts about what has happened, [Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
  are stored in a [Transactional Outbox](https://en.wikipedia.org/wiki/Inbox_and_outbox_pattern) and
  later published in [Protobuf](https://en.wikipedia.org/wiki/Protocol_Buffers) format using
  [Publish-Subscribe](https://www.enterpriseintegrationpatterns.com/patterns/messaging/PublishSubscribeChannel.html).
  A list of available messages [can be found here](https://github.com/gaming-platform/api).

  **Architecture**: Internally, it uses
  [Ports and Adapters](https://en.wikipedia.org/wiki/Hexagonal_architecture_(software)) to separate business logic
  from external systems. A [Mediator](https://en.wikipedia.org/wiki/Mediator_pattern) exposes the
  [Application Layer](https://martinfowler.com/eaaCatalog/serviceLayer.html), routing requests to handlers
  and handling cross-cutting concerns like validation and transaction management. Business logic is organized using a
  [Transaction Script](https://martinfowler.com/eaaCatalog/transactionScript.html).

  **Infrastructure**: MySQL is used to store chats, messages and events (Transactional Outbox), while Redis enables
  [Idempotent Receivers](https://www.enterpriseintegrationpatterns.com/patterns/messaging/IdempotentReceiver.html)
  to ensure that each message is processed exactly once, and RabbitMQ facilitates communication with other contexts.

  **Scalability**: The module is stateless, enabling it to scale horizontally by adding more instances.
  Current usage patterns of MySQL don’t require sharding, but chat IDs would be well-suited for partitioning if needed.
</details>

<details>
  <summary>Connect Four</summary>

  ### Connect Four

  **Purpose**: [Connect Four](/src/ConnectFour) handles games from players opening a game,
  through others joining and making moves, till they are finished (win, lose, or draw).

  **Communication**: Its use cases are exposed via
  [messaging](https://www.enterpriseintegrationpatterns.com/patterns/messaging/Messaging.html), utilizing
  [Request-Reply](https://www.enterpriseintegrationpatterns.com/patterns/messaging/RequestReply.html),
  with some directly invoked by the Web Interface to reduce network hops and abstractions.
  To notify other contexts about what has happened, [Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
  are stored in a [Transactional Outbox](https://en.wikipedia.org/wiki/Inbox_and_outbox_pattern) and
  later published in JSON format using
  [Publish-Subscribe](https://www.enterpriseintegrationpatterns.com/patterns/messaging/PublishSubscribeChannel.html).

  **Architecture**: Internally, it uses
  [Ports and Adapters](https://en.wikipedia.org/wiki/Hexagonal_architecture_(software)) to separate business logic
  from external systems. A [Mediator](https://en.wikipedia.org/wiki/Mediator_pattern) exposes the
  [Application Layer](https://martinfowler.com/eaaCatalog/serviceLayer.html), routing requests to handlers
  and handling cross-cutting concerns like validation and retries. Business logic is organized using
  [Domain Models](https://martinfowler.com/eaaCatalog/domainModel.html), stored as JSON documents because of their
  complexity. To keep the model focused on business logic and benefit from its scalability aspects,
  [CQRS](https://en.wikipedia.org/wiki/Command_Query_Responsibility_Segregation) is applied to separate reads and
  writes. Read models are maintained through projections that
  [asynchronously process a stream of domain events](https://en.wikipedia.org/wiki/Eventual_consistency).
  Applying CQRS at this level adds complexity
  ([busting CQRS myths](https://lostechies.com/jimmybogard/2012/08/22/busting-some-cqrs-myths/)),
  but the reasoning is explained in the Scalability section.

  **Infrastructure**: MySQL is used to store games (as JSON documents) and events (Transactional Outbox and
  [Stream Processing](https://en.wikipedia.org/wiki/Stream_processing)), while Redis stores read models because
  they don’t require relational queries, and RabbitMQ facilitates communication with other contexts.

  **Scalability**: The module is stateless, enabling it to scale horizontally by adding more instances.
  MySQL is sharded at application level using the game ID as the sharding key because it
  [became a bottleneck during load testing](https://github.com/marein/php-gaming-website/issues/119).
  ProxySQL enables [Schema-Based Sharding](https://proxysql.com/documentation/how-to-setup-proxysql-sharding/),
  allows the context to maintain only a single connection, and scales horizontally by being deployed as a
  [Sidecar](https://learn.microsoft.com/en-us/azure/architecture/patterns/sidecar).
  Current usage patterns of Redis don’t require any action.

  **Alternatives**: MySQL might not be the first choice for Stream Processing. Refer to "Messaging" in the
  [Technology Stack](#technology-stack) for the reasoning and alternatives.
</details>

<details>
  <summary>Identity</summary>

  ### Identity

  **Purpose**: [Identity](/src/Identity) manages all account types in the system. It supports the user's journey,
  starting from arrival as an anonymous user, through signup, to managing their profile, and it also handles bots,
  which are registered and managed programmatically.

  **Communication**: Its use cases are exposed via
  [messaging](https://www.enterpriseintegrationpatterns.com/patterns/messaging/Messaging.html), utilizing
  [Request-Reply](https://www.enterpriseintegrationpatterns.com/patterns/messaging/RequestReply.html),
  with some directly invoked by the Web Interface to reduce network hops and abstractions.
  To notify other contexts about what has happened, [Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html)
  are stored in a [transactional outbox](https://en.wikipedia.org/wiki/Inbox_and_outbox_pattern) and
  later published in [Protobuf](https://en.wikipedia.org/wiki/Protocol_Buffers) format using
  [Publish-Subscribe](https://www.enterpriseintegrationpatterns.com/patterns/messaging/PublishSubscribeChannel.html).
  A list of available messages [can be found here](https://github.com/gaming-platform/api).

  **Architecture**: Internally, it uses
  [Ports and Adapters](https://en.wikipedia.org/wiki/Hexagonal_architecture_(software)) to separate business logic
  from external systems. A [Mediator](https://en.wikipedia.org/wiki/Mediator_pattern) exposes the
  [Application Layer](https://martinfowler.com/eaaCatalog/serviceLayer.html), routing requests to handlers
  and handling cross-cutting concerns like validation and transaction management. Business logic is organized using
  [Domain Models](https://martinfowler.com/eaaCatalog/domainModel.html), which are managed by an
  [ORM](https://en.wikipedia.org/wiki/Object-relational_mapping) with features such as Class Table Inheritance.

  **Infrastructure**: MySQL is used to store users and events (Transactional Outbox), while RabbitMQ facilitates
  communication with other contexts.

  **Scalability**: The module is stateless, enabling it to scale horizontally by adding more instances.
  Current usage patterns of MySQL don’t require sharding, but a strategy similar to Connect Four would be necessary.
</details>

<details>
  <summary>Web Interface</summary>

  ### Web Interface

  > This module is currently being redesigned to aggregate fragments from each context for
  > higher [cohesion](https://en.wikipedia.org/wiki/Cohesion_(computer_science)), leveraging
  > [SSI](https://en.wikipedia.org/wiki/Server_Side_Includes) for
  > [UI composition](https://www.jimmybogard.com/composite-uis-for-microservices-a-primer/).
  > With this, there are two options for UI composition: on the client via
  > [Web Components](https://en.wikipedia.org/wiki/Web_Components) or on the server via SSI
  > (in addition to typical data aggregation).

  **Purpose**: [Web Interface](/src/WebInterface) ties all modules together and serves as the main point of
  interaction for users.

  **Communication**: It directly invokes use cases from other [modules](/src) to reduce network hops and abstractions,
  and calls other [services](https://github.com/gaming-platform?q=service-) via
  [Request-Response](https://en.wikipedia.org/wiki/Request–response).
  To notify users in real-time about what has happened, it subscribes to events from other contexts using
  [Publish-Subscribe](https://www.enterpriseintegrationpatterns.com/patterns/messaging/PublishSubscribeChannel.html),
  and forwards them to subscribed users via [Server-Sent Events](https://en.wikipedia.org/wiki/Server-sent_events).

  **Architecture**: Internally, it uses a form of
  [Layered Architecture](https://en.wikipedia.org/wiki/Multitier_architecture) server-side. To reduce client-side
  complexity, the [REST architectural style](https://en.wikipedia.org/wiki/REST) is used for browser interactions
  wherever possible. For client-side heavy features, like real-time notifications or handling
  [Eventual Consistency](https://en.wikipedia.org/wiki/Eventual_consistency), it leverages web standards,
  such as [Web Components](https://en.wikipedia.org/wiki/Web_Components), reducing maintenance effort significantly
  due to the long-term stability of the web.

  **Infrastructure**: Redis is used to store sessions, while Nchan notifies users in real-time, and RabbitMQ
  facilitates communication with other contexts.

  **Scalability**: The module is stateless, enabling it to scale horizontally by adding more instances.
  Some queues can be sharded using RabbitMQ's
  [Consistent Hash Exchange](https://github.com/rabbitmq/rabbitmq-server/blob/main/deps/rabbitmq_consistent_hash_exchange/README.md)
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

> Additional resources, such as [infrastructure components](https://github.com/gaming-platform?q=docker-), can
> be found in the [Gaming Platform](https://github.com/gaming-platform) organization.

## Technology Stack

The platform uses a minimal set of infrastructure components to keep complexity and maintenance overhead low,
while expanding its stack only when necessary to meet specific performance or scalability requirements.

Learn more about the technology stack and the reasons behind each choice below.

<details>
  <summary>Languages and Frameworks</summary>

  ### Languages and Frameworks

  * **PHP & Symfony**: The main language and framework used in the platform. Both are mature, offer a large ecosystem,
    and provide solid performance with good scalability. Refer to "Production for Load Testing" within the
    [Deployment Guide](#deployment-guide) to see how the platform performs under load.
  * **HTML/CSS/JavaScript**: Sticking to web standards as much as possible ensures stability and minimizes maintenance
    overhead. Modern features like Web Components and Import Maps enhance modularity and reduce the need for additional
    frameworks and tooling.
  * **Tabler**: A design system used to provide a consistent UI across the platform, reducing development
    time by offering pre-built components.

  > Some features may be implemented in other languages, such as Go or C#, where efficient use of all CPU cores
  > would be beneficial - for example, in a [computer player](https://github.com/marein/php-gaming-website/issues/122)
  > or [matchmaker](https://github.com/marein/php-gaming-website/issues/121).
</details>

<details>
  <summary>Storage</summary>

  ### Storage

  * **MySQL**: A reliable database used to handle both relational and non-relational transactional data, essential
    for supporting a [Transactional Outbox](https://en.wikipedia.org/wiki/Inbox_and_outbox_pattern).
  * **ProxySQL**: Deployed as a [Sidecar](https://learn.microsoft.com/en-us/azure/architecture/patterns/sidecar) to
    route database traffic, manage connection pooling, and optimize query performance. It supports
    [Schema-Based Sharding](https://proxysql.com/documentation/how-to-setup-proxysql-sharding/) and ensures efficient
    load balancing across MySQL instances.
  * **Redis**: Employed to manage user sessions, store read models, and implement
    [Idempotent Receivers](https://www.enterpriseintegrationpatterns.com/patterns/messaging/IdempotentReceiver.html),
    leveraging its in-memory data structure for high-performance operations.
</details>

<details>
  <summary>Messaging</summary>

  ### Messaging

  * **RabbitMQ**: Utilized for reliable inter-service communication, supporting both
    [Request-Reply](https://www.enterpriseintegrationpatterns.com/patterns/messaging/RequestReply.html) and
    [Publish-Subscribe](https://www.enterpriseintegrationpatterns.com/patterns/messaging/PublishSubscribeChannel.html)
    messaging patterns to facilitate temporal decoupling.
  * **Nchan**: Provides a scalable, persistent
    [Publish-Subscribe](https://www.enterpriseintegrationpatterns.com/patterns/messaging/PublishSubscribeChannel.html)
    messaging system for real-time browser notifications, ensuring low-latency between clients and servers.
  * **MySQL**: Used to publish [Domain Events](https://martinfowler.com/eaaDev/DomainEvent.html) stored in the
    [Transactional Outbox](https://en.wikipedia.org/wiki/Inbox_and_outbox_pattern) reliably to other messaging systems,
    and to perform [Stream Processing](https://en.wikipedia.org/wiki/Stream_processing) for building read models within
    a given context using those same events.
  * **Protobuf & JSON**: The chosen message formats for inter-service communication. While JSON messages are not
    defined using [JSON Schema](https://json-schema.org) to avoid added complexity, Protobuf schema definitions
    [can be found here](https://github.com/gaming-platform/api).

  > MySQL is used for Stream Processing because Domain Events are already stored in the Transactional Outbox and need
  > to be published to messaging systems as it already does with RabbitMQ. This avoids additional complexity as long as
  > MySQL scales effectively (>20k events/s per shard). If increased streaming processes impact database performance
  > or if inter-service streaming is required, alternatives like
  > [RabbitMQ’s Super Streams](https://www.rabbitmq.com/docs/streams#super-streams) or
  > [Kafka](https://kafka.apache.org) will be considered.
</details>

<details>
  <summary>Observability</summary>

  ### Observability

  * **Grafana & Prometheus**: A combined solution for real-time monitoring and visualization, where Prometheus
    collects and stores metrics, and Grafana provides dashboards and alerts. The dashboard definitions
    [can be found here](https://github.com/gaming-platform/docker-grafana).
</details>
