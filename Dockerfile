FROM php:7.4.19-cli
LABEL maintainer="neto.joaobatista@gmail.com"

RUN apt -y update && apt -y install libzip-dev libxml2-dev unzip
RUN docker-php-ext-install soap zip

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /bin/composer

WORKDIR /src/globalpagamentos

CMD composer install && ./vendor/bin/phpunit --colors='always'
