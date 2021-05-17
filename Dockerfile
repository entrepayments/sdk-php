FROM php:7.4.19-cli
LABEL maintainer="neto.joaobatista@gmail.com"

RUN apt -y update && apt -y install libzip-dev libxml2-dev
RUN docker-php-ext-install soap zip

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /bin/composer

ADD ./src /src/globalpagamentos/src
ADD ./tests /src/globalpagamentos/tests
ADD ./composer.json /src/globalpagamentos/composer.json
ADD ./phpunit.xml /src/globalpagamentos/phpunit.xml

WORKDIR /src/globalpagamentos

RUN composer install

RUN echo "./vendor/bin/phpunit --colors='always'" >tests.sh

CMD sh tests.sh
