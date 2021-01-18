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

## Translations

Import translations by running

```sh
(cd html && ../vendor/bin/drush locale:import --type=customized --override=all da ../translations/custom-translations.da.po)
```

Export translations by running

```sh
(cd html && ../vendor/bin/drush locale:export da --types=customized > ../translations/custom-translations.da.po)
```

Open `translations/custom-translations.da.po` with the latest version of
[Poedit](https://poedit.net/) to clean up and then save the file.

See
https://medium.com/limoengroen/how-to-deploy-drupal-interface-translations-5653294c4af6
for further details.


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
