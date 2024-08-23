#!/bin/bash
set -e

echo "Content-Type: application/xspf+xml"
echo ""
php /opt/stream/scripts/list.php 2>&1
php /opt/stream/scripts/generate-playlist.php 2>&1
cat /opt/stream/dash/playlist_wrapper.xspf

exit 0
