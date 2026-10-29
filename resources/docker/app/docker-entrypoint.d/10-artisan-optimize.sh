#!/bin/sh

set -e

php artisan package:discover
php artisan config:cache
php artisan route:cache
php artisan view:cache
