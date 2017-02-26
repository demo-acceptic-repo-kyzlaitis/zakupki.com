#!/bin/bash
sleep 1m
/usr/bin/supervisord
./docker/php-fpm/start.sh