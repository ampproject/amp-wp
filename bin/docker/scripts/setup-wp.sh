#!/usr/bin/env bash

set -ex

wp --version

wp core install \
    --url=ampwp.local \
    --title="AMP for WP" \
    --admin_user=admin \
    --admin_password=password \
    --admin_email=admin@example.com
