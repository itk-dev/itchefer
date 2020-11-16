# IT Chefer
Based on [Open social distribution](https://github.com/goalgorilla/open_social)

## Installation instructions
Setup Docker environment (Optional):
```
itkdev-docker-compose up -d
```
Get php packages
```
itkdev-docker-compose composer install
```
Setup settings.php file:
```
cp html/sites/default/_docker.settings.local.php html/sites/default/docker.settings.local.php 
```
Install site: 
```
itkdev-docker-compose vendor/bin/drush site-install minimal
```
Sync DB from remote site:
```
itkdev-docker-compose sync
```
Import config to be sure we have the latest:
```
itkdev-docker-compose vendor/bin/drush config-import
```
Get to work
```
itkdev-docker-compose open
```

## Drush
One time login:
```
itkdev-docker-compose vendor/bin/drush uli
```
Clear cache
```
itkdev-docker-compose vendor/bin/drush cr
```
Other stuff
```
itkdev-docker-compose vendor/bin/drush
```
