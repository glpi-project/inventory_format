GLPI Inventory format
---------------------

This is the specification for inventory format integrated in GLPI core.

It relies on [JSON Schema](https://json-schema.org).

The [inventory.schema.json](inventory.schema.json) file is the JSON schema itself.

The [lib/php](lib/php) directory contains a PHP class that cans handle conversion from XML files to new format; with some adjustments. You can either use directly ``Convert`` from your project, or rely on the [executable script](bin/convert) provided.

## Run unit tests 

You can use the local PHP environment or the provided Docker image to run unit tests.

### Localhost

```sh
composer test
```
### With Docker Compose

```sh
docker compose run --rm ci
```

To use another PHP version:

```sh
PHP_VERSION=7.4 docker compose run --rm --build ci
```

PHP_VERSION can be set from 7.4 to 8.5 (default is 8.3).

In case, you need a shell in the container to run some tests manually:

```sh
docker compose run --rm ci bash
