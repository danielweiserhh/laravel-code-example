FROM php:8.2.12-fpm-bullseye

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && docker-php-ext-enable pdo_pgsql pgsql \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && echo "pcov.enabled=1" >> /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini \
    && echo "pcov.directory=/var/www/html" >> /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer

RUN echo '#!/bin/bash' > /usr/local/bin/docker-entrypoint.sh && \
    echo 'set -e' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '# Install PCOV if not present (for code coverage)' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'EXT_DIR=$(php-config --extension-dir)' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'if [ ! -f "$EXT_DIR/pcov.so" ]; then' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    cd /tmp && pecl download pcov >/dev/null 2>&1 && tar -xzf pcov-*.tgz && cd pcov-* && phpize >/dev/null 2>&1 && ./configure >/dev/null 2>&1 && make >/dev/null 2>&1 && cp modules/pcov.so "$EXT_DIR/" && docker-php-ext-enable pcov >/dev/null 2>&1 || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'fi' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '# Fix permissions - ensure storage and bootstrap/cache are writable' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'if [ -d "/var/www/html/storage" ]; then' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    chown -R 1000:1000 /var/www/html/storage 2>/dev/null || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    chmod -R 775 /var/www/html/storage 2>/dev/null || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    # Ensure logs directory exists and is writable' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    mkdir -p /var/www/html/storage/logs /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views 2>/dev/null || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    chown -R 1000:1000 /var/www/html/storage/logs /var/www/html/storage/framework 2>/dev/null || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    chmod -R 775 /var/www/html/storage/logs /var/www/html/storage/framework 2>/dev/null || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'fi' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'if [ -d "/var/www/html/bootstrap/cache" ]; then' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    chown -R 1000:1000 /var/www/html/bootstrap/cache 2>/dev/null || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo '    chmod -R 775 /var/www/html/bootstrap/cache 2>/dev/null || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'fi' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'find /var/www/html -not -path "*/vendor/*" -not -path "*/node_modules/*" -type f -user root -exec chown 1000:1000 {} \; 2>/dev/null || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'find /var/www/html -not -path "*/vendor/*" -not -path "*/node_modules/*" -type d -user root -exec chown 1000:1000 {} \; 2>/dev/null || true' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'exec "$@"' >> /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

RUN mkdir -p /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    /var/www/.composer/cache

RUN sed -i 's/user = www-data/user = 1000/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = 1000/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/listen.owner = www-data/listen.owner = 1000/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/listen.group = www-data/listen.group = 1000/' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf

RUN chown -R 1000:1000 /var/www/html /var/www/.composer && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 755 /var/www/html

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=10s --retries=3 --start-period=40s \
    CMD php -r "exit(0);" || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
