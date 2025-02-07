#!/bin/bash

# idealne napsat php script, ktery by mohl automaticky moduly instalovat... bylo by to podobny install scriptu
echo '
-------------------------------------------------------

--
-- Automatic module registration
--

INSERT INTO `oc_extension_install` (`extension_install_id`, `extension_id`, `extension_download_id`, `name`, `description`, `code`, `version`, `author`, `link`, `status`, `date_added`)
VALUES (2, 0, 0, "CartSMS module for OpenCart", "", "oc_cartsms", "4.0", "BulkGate", "https://www.bulkgate.com/en/integrations/cartsms-sms-module-for-opencart/", 1, "2020-08-29 15:35:39");

' >> /var/www/html/install/opencart-en-*.sql

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
    touch /var/www/html/install.lock
fi

exec apache2-foreground