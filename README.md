# IT Chefer

Based on [Open social distribution](https://github.com/goalgorilla/open_social)

## Installation instructions

Setup Docker environment (Optional):

```sh
itkdev-docker-compose up -d
```

Get php packages

```sh
itkdev-docker-compose composer install
```

Setup settings.php file:

```sh
cp html/sites/default/_docker.settings.local.php html/sites/default/docker.settings.local.php
```

For development, you also want developer settings:

```sh
cp html/sites/example.settings.local.php html/sites/default/settings.local.php
```

Install site:

```sh
itkdev-docker-compose vendor/bin/drush site-install minimal
```

Sync DB from remote site:

```sh
itkdev-docker-compose sync
```

Import config to be sure we have the latest:

```sh
itkdev-docker-compose vendor/bin/drush config-import
```

Get to work

```sh
itkdev-docker-compose open
```

## Drush

One time login:

```sh
itkdev-docker-compose vendor/bin/drush uli
```

Clear cache

```sh
itkdev-docker-compose vendor/bin/drush cr
```

Other stuff

```sh
itkdev-docker-compose vendor/bin/drush
```

## Developing with symfony binary

Install `symfony`: [https://symfony.com/download](https://symfony.com/download)

```sh
docker-compose up -d
symfony composer install
symfony php vendor/bin/drush site:install social --config=config/sync --yes
symfony local:server:start --document-root=html
```

Make sure that your database settings in
`html/sites/default/docker.settings.local.php` are defined as

```php
$databases['default']['default'] = [
  'database' => getenv('DATABASE_DATABASE') ?: 'db',
  'username' => getenv('DATABASE_USERNAME') ?: 'db',
  'password' => getenv('DATABASE_PASSWORD') ?: 'db',
  'host' => getenv('DATABASE_HOST') ?: 'mariadb',
  'port' => getenv('DATABASE_PORT') ?: '',
  'driver' => getenv('DATABASE_DRIVER') ?: 'mysql',
  'prefix' => '',
];
```
