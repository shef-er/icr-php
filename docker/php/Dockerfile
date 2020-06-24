FROM php:7.4.5-fpm-alpine

# RUN set -ex \
#     && { \
# 		echo '[global]'; \
# 		echo 'daemonize = no'; \
# 		echo; \
# 		echo '[www]'; \
# 		echo 'listen = 0.0.0.0:9000'; \
# 	} | tee /usr/local/etc/php-fpm.d/zz-docker.conf;

RUN set -ex \
    && apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql

RUN wget https://getcomposer.org/installer -O - -q \
    | php -- --install-dir=/bin --filename=composer --quiet

CMD ["php-fpm"]

EXPOSE 9000
