#!/bin/bash
sleep 10
/usr/bin/indexer -c /etc/sphinxsearch/sphinxy.conf --all --rotate
./cron.sh
./searchd.sh
