#!/bin/bash
set -e

LOGFILE="/opt/stream/log/dvd-event.log"

/opt/stream/ffmpeg_dash.sh >> $LOGFILE

exit 0
