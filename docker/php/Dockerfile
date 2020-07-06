FROM thecodingmachine/php:7.2-v3-fpm-node8

ENV PHP_EXTENSION_INTL=1 \
    PHP_EXTENSION_GD=1 \
    PHP_EXTENSION_BCMATH=1 \
    PHP_EXTENSION_IMAGICK=1 \
    PHP_EXTENSION_MONGODB=1 \
    PHP_EXTENSION_XDEBUG=1

USER root

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends bash-completion;\
    rm -rf /var/lib/apt/lists/*

RUN mv /usr/local/bin/real_composer /usr/local/bin/composer

RUN set -eux; \
    curl https://bitbucket.org/eactive/docker-utils/raw/master/bash/.bashrc --output /root/.bashrc; \
    cp /root/.bashrc /home/docker/.bashrc; \
    chown docker:docker /home/docker/.bashrc

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends ssmtp; \
    rm -rf /var/lib/apt/lists/*; \
    { \
        echo "root=postmaster"; \
        echo "mailhub=mail:1025"; \
        echo "FromLineOverride=YES"; \
    } > /etc/ssmtp/ssmtp.conf; \
    sed -i 's/^;sendmail_path .*/sendmail_path = \/usr\/sbin\/ssmtp -t/g' /usr/lib/php/${PHP_VERSION}/php.ini-development; \
    sed -i 's/^;sendmail_path .*/sendmail_path = \/usr\/sbin\/ssmtp -t/g' /usr/lib/php/${PHP_VERSION}/php.ini-development.cli

USER docker

RUN echo 'source ~/.bashrc' > ~/.bash_profile; \
    echo 'eval "$(symfony-autocomplete --shell=bash)"' >> ~/.bashrc

USER root

RUN npm install -g less@1.7.5 bower uglify-js uglifycss;

RUN set -eux; \
    curl https://www.mongodb.org/static/pgp/server-4.0.asc | sudo apt-key add -; \
    echo "deb [ arch=amd64 ] https://repo.mongodb.org/apt/ubuntu bionic/mongodb-org/4.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-4.0.list; \
    apt-get update; \
    apt-get install -y --no-install-recommends mongodb-org-tools mariadb-client-10.1;\
    apt-get install -y --no-install-recommends zip;\
    rm -rf /var/lib/apt/lists/*

USER docker

EXPOSE 9000
