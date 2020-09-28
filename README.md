# IT Chefer
- Open social distribution

## Installation instructions

Setup Docker environment (Optional):
```
itk-dev-docker-compose up -d
```
Setup settings.php file
```
cp html/sites/default/_docker.settings.local.php html/sites/default/docker.settings.local.php 
```
Install site (Takes a long time): 
```
itkdev-docker-compose vendor/bin/drush site-install minimal
```
Update UUID key for site
```
itkdev-docker-compose sync
```
Import config
```
itkdev-docker-compose vendor/bin/drush config-import
```