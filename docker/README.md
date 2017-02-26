docker-zakupki
==============

Docker multi-container application to have a complete stack for running market and price projects into Docker containers using docker-compose tool.

# Installation

First, clone this repository:

```bash
$ git clone git@bitbucket.org:zakupkicomua/zakupki.git

```

Then, run:

```bash
$ sudo docker-compose up -d
```

You are done, you can visit your application on the following URL: `http://zakupki.doc`

Optionally, you can build your Docker images separately by running:

```bash
$ docker build -t docker/application application
$ docker build -t docker/php-fpm php-fpm
$ docker build -t docker/nginx nginx
```

# How it works?

Here are the `docker-compose` built images:

* `application`: This is the docker application code container,
* `db`: This is the MySQL database container (can be changed to mysql or whatever in `docker-compose.yml` file),
* `php`: This is the PHP-FPM container in which the application volume is mounted,
* `nginx`: This is the Nginx webserver container in which application volume is mounted too


# Read logs

You can access Nginx logs in the following directories into your host machine: `nginx/log`

