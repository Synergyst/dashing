#!/bin/bash
set -e

#echo "Content-Type: text/plain"
echo "Content-Type: text/html"
echo ""
php /opt/stream/scripts/list.php 2>&1
php /opt/stream/scripts/generate-playlist.php 2>&1
php /opt/stream/scripts/movies.php "$QUERY_STRING" 2>&1

exit 0
