ARG PHP_VERSION
FROM wordpress:$PHP_VERSION

# install wp-cli
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp

# install xdebug
RUN apk add --update --virtual build_deps gcc g++ autoconf make linux-headers \
  && pecl install xdebug \
    && docker-php-ext-enable xdebug

# install MailHugs mhsendmail
RUN curl -LkSso /usr/local/bin/mhsendmail 'https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64' \
    && chmod 0755 /usr/local/bin/mhsendmail;
