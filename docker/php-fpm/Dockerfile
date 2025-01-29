FROM php:8.3-fpm

RUN apt-get update \
    && apt-get install -y git zlib1g-dev libonig-dev libicu-dev libxext6 libxml2-utils libxml2-dev libzip-dev zip\
    && rm -rf /var/lib/apt/lists/*

RUN pecl install -o -f xdebug apcu pcov
RUN docker-php-ext-enable xdebug apcu pcov

# Zeitzone einrichten
ENV TZ Europe/Berlin
RUN echo $TZ > /etc/timezone && \
    apt-get update && apt-get install -y tzdata && \
    rm /etc/localtime && \
    ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install fileinfo intl mbstring opcache pdo_mysql bcmath zip xml dom
RUN mkdir /var/log/php && touch /var/log/php/php_errors.log && ln -sf /dev/stderr /var/log/php/php_errors.log

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Local PHP Security Checker
RUN curl -sSL https://github.com/fabpot/local-php-security-checker/releases/download/v2.1.3/local-php-security-checker_linux_386 --output local-php-security-checker && chmod +x local-php-security-checker

#RUN composer install && echo "eval "$(/var/www/project/bin/console completion bash)"" > /root/.bashrc
