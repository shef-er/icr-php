## SETUP

```sh
## Install PHP with extensions (for composer)
sudo apt install php php-mbstring php-xml php-json;

cd backend;

## Install Composer 
## From https://getcomposer.org/download/
## Or run this sript
sh bin/composer-install.sh

## Install dependencies
php composer.phar install;

## Set up configration file for database (if needed)
nano config/defines.php;

cd ../;

## Launch docker compose
docker-compose up --build -d
```

### Results
Application and it's API should be running on localhost:80.

Adminer on localhost:8080.
