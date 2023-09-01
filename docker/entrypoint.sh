#!/bin/bash

set -euo pipefail
cd /

usermod -u "$(sudo -u www-data stat -c %u /data/README.md)" www-data

exec docker-php-entrypoint apache2-foreground
