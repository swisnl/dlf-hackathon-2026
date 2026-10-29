ARG IMAGE_BASE_APP=swisleiden/base-php-8.4-fpm:1
ARG IMAGE_BASE_WEB=swisleiden/base-nginx:1
ARG IMAGE_BUILDER_PHP=swisleiden/base-php-8.4-fpm:1-dev
ARG IMAGE_BUILDER_NODE=node:24

# Base images
# ===========

# Install any additional (binary) dependencies for the app server.
FROM $IMAGE_BASE_APP AS app-base

COPY resources/docker/app/docker-entrypoint.d/ /docker-entrypoint.d/

# Install any additional (binary) dependencies for the web server.
FROM $IMAGE_BASE_WEB AS web-base

COPY resources/docker/nginx/nginx-conf.d /etc/nginx/templates/

# Install any additional (binary) dependencies for the builder.
FROM $IMAGE_BUILDER_PHP AS builder-base

# Install any additional (binary) dependencies for the node builder.
FROM $IMAGE_BUILDER_NODE AS builder-node-base

WORKDIR /srv

# Builder images
# ==============

# Install PHP dependencies.
FROM builder-base AS builder-php

COPY composer.* /srv/

RUN composer install \
    --no-ansi \
    --no-interaction \
    --no-dev \
    --no-scripts \
    --no-autoloader

# Install node dependencies and build assets.
FROM builder-node-base AS builder-node

COPY \
  # Sources:
  package.json \
  package-lock.json \
  vite.config.js \
  # Destination:
  /srv/

RUN npm install

COPY resources/css   /srv/resources/css
COPY resources/js    /srv/resources/js

RUN npm run build

# Combine results of the builders.
FROM builder-php AS builder

# Note that the order is based upon the size of the
# directories and how often things typically change.

#COPY --from=builder-php  /srv/vendor             /srv/vendor
COPY --from=builder-node /srv/public_html        /srv.build/public_html

COPY artisan            /srv/artisan
COPY storage            /srv/storage
COPY bootstrap          /srv/bootstrap
COPY config             /srv/config
#COPY lang               /srv/lang
COPY database           /srv/database
COPY composer.*         /srv/
COPY routes             /srv/routes
COPY app                /srv/app
COPY resources/views    /srv/resources/views
COPY public_html        /srv/public_html

RUN cp -prf /srv.build/* /srv/ \
  && rm -r /srv.build \
  && composer dump-autoload --optimize --no-scripts \
  && composer publish-assets \
  && chgrp -R 0 /srv/storage/ \
  && chmod -R g=u /srv/storage/

# Production images
# =================

# Finalize the app server image.
FROM app-base AS app

COPY --from=builder /srv /srv

VOLUME /srv/storage/app

# This image supports to be run by an arbitrary uid.
# The primary gid should be root (0).
# The default uid is nonroot (10001) but can be any uid.
# To support this, directories and files that are written to by processes in
# the image must be owned by the root group and be read/writable by that group.
# Files to be executed must also have group execute permissions.
USER nonroot:root

# https://github.com/opencontainers/image-spec/blob/main/annotations.md
ARG NAME
ARG VERSION
ARG CREATED
ARG SOURCE
ARG REVISION=HEAD
LABEL \
  org.opencontainers.image.title="$NAME" \
  org.opencontainers.image.description="App server for $NAME using PHP FPM." \
  org.opencontainers.image.version="$VERSION" \
  org.opencontainers.image.created="$CREATED" \
  org.opencontainers.image.source="$SOURCE" \
  org.opencontainers.image.revision="$REVISION"

# Finalize the web server image.
FROM web-base AS web

COPY --from=builder --exclude=*.php /srv/public_html /srv/public_html

# This image supports to be run by an arbitrary uid.
USER nonroot:root

ARG NAME
ARG VERSION
ARG CREATED
ARG SOURCE
ARG REVISION=HEAD
LABEL \
  org.opencontainers.image.title="$NAME/web" \
  org.opencontainers.image.description="Web server for $NAME using NGINX." \
  org.opencontainers.image.version="$VERSION" \
  org.opencontainers.image.created="$CREATED" \
  org.opencontainers.image.source="$SOURCE" \
  org.opencontainers.image.revision="$REVISION"
