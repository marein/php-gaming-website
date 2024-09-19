# php-gaming-website

__Table of contents__

* [Overview](#overview)
* [Deploy the app](#deploy-the-app)

## Overview

This is a web-based board game platform designed for players to connect and play.
Alongside the gaming experience, it showcases a range of software engineering concepts, including a modular,
reactive, domain-driven backend architecture that ensures scalability, real-time browser interactions,
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
