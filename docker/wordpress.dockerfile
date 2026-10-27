ARG PHP_VERSION
FROM wordpress:$PHP_VERSION

# install wp-cli
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp

# install xdebug
RUN apk add --update --virtual build_deps gcc g++ autoconf make linux-headers \
  && pecl install xdebug \
    && docker-php-ext-enable xdebug

