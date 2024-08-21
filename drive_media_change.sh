#!/bin/bash

LOGFILE="/opt/stream/log/dvd-event.log"

echo "$(date): Event $1 triggered" >> $LOGFILE
echo "$(date): Event $1 triggered" > /dev/kmsg

case "$1" in
    mediaejected)
        echo "DVD ejected" >> $LOGFILE
        echo "DVD ejected" > /dev/kmsg
        # Commands for when the DVD is ejected
        ;;
    mediainserted)
        echo "New media inserted" >> $LOGFILE
        echo "New media inserted" > /dev/kmsg
        # Commands for when new media is inserted
        sleep 10
        (su - root -c 'screen -d -m /opt/stream/ffmpeg_dash_wrapper.sh')
        #/opt/stream/ffmpeg_dash.sh >> $LOGFILE
        ;;
    *)
        echo "Invalid option: $1" >> $LOGFILE
        echo "Invalid option: $1" > /dev/kmsg
        ;;
esac
