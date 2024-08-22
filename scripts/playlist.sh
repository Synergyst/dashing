#!/bin/bash

echo "Content-Type: application/xspf+xml"
echo ""
php /opt/stream/scripts/list.php
php /opt/stream/scripts/generate-playlist.php
cat /opt/stream/dash/playlist_wrapper.xspf

exit 0
