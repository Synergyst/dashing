#!/bin/bash

#echo "Content-Type: text/plain"
echo "Content-Type: text/html"
echo ""
php /opt/stream/scripts/list.php
php /opt/stream/scripts/generate-playlist.php
# Extract the value of the 'search' parameter from the QUERY_STRING
search_param=$(echo "$QUERY_STRING" | grep -oP '(?<=^|&)search=\K[^&]+')
php /opt/stream/scripts/movies.php "search=$search_param"

exit 0
