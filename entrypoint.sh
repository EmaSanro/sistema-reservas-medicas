#!/bin/bash
cd /var/www/html
composer install --no-interaction --optimize-autoloader --no-scripts
apache2-foreground