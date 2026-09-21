FROM php:8.5-fpm

ARG UID=1000
ARG GID=1000

# ─────────────────────────────
# 1. Dependências de sistema
# ─────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip zip curl \
    procps \
    supervisor \
    libzip-dev \
    libicu-dev libpq-dev

# ─────────────────────────────
# 2. Extensões PHP nativas
# ─────────────────────────────
RUN docker-php-ext-install \
    pdo_pgsql \
    pcntl \
    zip \
    intl

# ─────────────────────────────
# 3. Redis (phpredis)
# ─────────────────────────────
RUN pecl install redis \
    && docker-php-ext-enable redis

# ─────────────────────────────
# 4. Composer
# ─────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ─────────────────────────────
# 5. Limpeza de pacotes temporários
# ─────────────────────────────
RUN apt-get autoremove -y && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# ─────────────────────────────
# 6. Configuração do php.ini (development como base)
# ─────────────────────────────
RUN for INI in "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini-production"; do \
      sed -i \
        -e "s/expose_php = On/expose_php = Off/g" \
        -e "s/;realpath_cache_size = 4096k/realpath_cache_size = 4096k/g" \
        -e "s/;realpath_cache_ttl = 120/realpath_cache_ttl = 600/g" \
        -e "s|;date.timezone =|date.timezone = America/Sao_Paulo|g" \
        -e "s/max_execution_time = 30/max_execution_time = 150/g" \
        -e "s/max_input_time = 60/max_input_time = 300/g" \
        -e "s/post_max_size = 8M/post_max_size = 300M/g" \
        -e "s/upload_max_filesize = 2M/upload_max_filesize = 300M/g" \
        -e "s/memory_limit = 128M/memory_limit = 512M/g" \
        "$INI"; \
    done \
    && sed -i -e "s|^;*error_log =.*|error_log = /proc/self/fd/2|" "$PHP_INI_DIR/php.ini-production" \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# OPcache (built-in no PHP 8.5, apenas configurado aqui)
COPY docker/php/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini

# ─────────────────────────────
# 7. Ajuste do pool FPM (tuning)
# ─────────────────────────────
RUN { \
      echo 'pm = dynamic'; \
      echo 'pm.max_children = 20'; \
      echo 'pm.start_servers = 10'; \
      echo 'pm.max_spare_servers = 10'; \
      echo 'pm.min_spare_servers = 5'; \
      echo 'pm.max_requests = 1000'; \
    } >> /usr/local/etc/php-fpm.d/zz-docker.conf

# ─────────────────────────────
# 8. Wrapper do php CLI (carrega cli.ini)
# ─────────────────────────────
COPY docker/php/cli.ini /usr/local/etc/php/cli/conf.d/99-cli.ini
RUN mv /usr/local/bin/php /usr/local/bin/php.real
COPY docker/php/php-cli /usr/local/bin/php
RUN chmod +x /usr/local/bin/php

# ─────────────────────────────
# 9. Usuário www sincronizado com UID/GID do host
# ─────────────────────────────
RUN if getent group ${GID}; then \
      group_name=$(getent group ${GID} | cut -d: -f1); \
      useradd -m -u ${UID} -g ${GID} -s /bin/bash www; \
    else \
      groupadd -g ${GID} www && \
      useradd -m -u ${UID} -g www -s /bin/bash www; \
      group_name=www; \
    fi \
    && sed -i "s/user = www-data/user = www/g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = $group_name/g" /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

# ─────────────────────────────
# 10. Entrypoint + Supervisor (php-fpm + horizon)
# ─────────────────────────────
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
COPY docker/supervisord.conf /usr/local/supervisord.conf

USER www

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
