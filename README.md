# ebookmarket

Ebook selling website developed for the course of Systems and Network Hacking
(Computer Engineering, UNIPI)

## Setup

### Requirements

- PHP >= 7.3
- Apache with `mod_rewrite` is recommended.

### Setup With docker (Linux only)

The docker images inside the [docker](docker/) folder can be used to easily
setup the environment for the application under Linux. Docker Compose >= 1.27
is needed.

```
$ cd docker/
$ docker-compose up -d
$ cd ..
$ mysql -h 127.0.0.1 -u dbuser -p
Enter password: dbpass
mysql> source db.sql
mysql> source populate.sql
```

The application should now be reachable at
[https://localhost:8443](https://localhost:8443) (for HTTPS issues, see below).

### Other systems

The application works also under Windows or Mac: just install it manually
(docker does not work due to networking limitations in these systems).

To enable HTTPS, you can use the [certificate](docker/web/certs/localhost.crt)
and the [private key](docker/web/private/localhost.key) we provided; or create
your own keys (see below).

### Configuration

The application needs to be configured.

```
$ cd src/include/
$ cp config{.orig,}.php
$ vim config.php
```

The most important configuration directives are at the top of the file.

#### HTTPS

To avoid the browser warning about the missing certificate, add the [CA
certificate](docker/web/rootCA.crt) to the list of trusted root CA certificates
of your browser.

Alternatively, you can create your own certificates: follow the instructions
available in the [Dockerfile](docker/web/Dockerfile).
