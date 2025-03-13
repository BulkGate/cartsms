#!/bin/bash

if [ ! -f /var/www/html/install.lock ]; then
    wait-for-it db:3306 -t 60 &&
    php /var/www/html/install/cli_install.php install \
        --username admin \
        --password admin \
        --email admin@example.com \
        --http_server http://localhost:8083/ \
        --db_driver mysqli \
        --db_hostname db \
        --db_username root \
        --db_password admin \
        --db_database opencart \
        --db_port 3306 \
        --db_prefix oc_ &&
    touch /var/www/html/install.lock &&
    php /tmp/install-extension.php
fi

exec apache2-foreground