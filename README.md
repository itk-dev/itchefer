# IT Chefer
- Open social distribution

## Installation instructions
Setup Docker environment (Optional):
```
itk-dev-docker-compose up -d
```
Get php packages
```
itk-dev-docker-compose composer install
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