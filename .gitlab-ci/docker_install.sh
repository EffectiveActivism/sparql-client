#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer
apt-get update -yqq
apt-get install git wget -yqq

# Install xdebug to enable code coverage
pecl install xdebug
echo "xdebug.mode=coverage" > /usr/local/etc/php/conf.d/xdebug.ini
