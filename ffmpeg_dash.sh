#!/bin/bash
set -e

dvd_dash_path="/opt/stream/dash"
dvd_drive_path="/dev/sr0"
file_ext=".mp4"
log_file_handbrake="/opt/stream/log/handbrake-transcode-job.log"
log_file_media_check="/opt/stream/log/media-check.log"
log_file_ffmpeg="/opt/stream/log/ffmpeg-transcode-job.log"
dvd_file_name="$(lsdvd "$dvd_drive_path" |grep 'Disc Title:'|cut -f3 -d' ')"
dash_vod_folder="$dvd_dash_path/VOD_$dvd_file_name"
dvd_raw_path="$dash_vod_folder/raw"

dvd_file_name_file_ext_with_path="$dvd_raw_path/$dvd_file_name$file_ext"
#mkdir -p "$dash_vod_folder/"
mkdir -p "$dvd_raw_path/"
if [ -n "$(ls -A $dash_vod_folder/ 2>/dev/null)" ]; then
  #echo "contains files (or is a file)"
  if [ -n "$(ls -A $dash_vod_folder/watch.mpd 2>/dev/null)" ]; then
    echo "We already have ripped this. TODO: write a handler to reconfigure database for this movie or something else."
    echo "We already have ripped this. TODO: write a handler to reconfigure database for this movie or something else." >> "$log_file_media_check"
    read temp
    eject "$dvd_drive_path"
    exit 1
  fi
  if [ -n "$(ls -A $dvd_raw_path/ 2>/dev/null)" ]; then
    echo "Raw master copy directory empty. We have been here before, though something clearly went wrong in a previous rip.."
    echo "Raw master copy directory empty. We have been here before, though something clearly went wrong in a previous rip.." >> "$log_file_media_check"
    read temp
    eject "$dvd_drive_path"
    exit 1
  fi
#else
  #echo "empty (or does not exist)"
fi
cd "$dvd_raw_path/"
# NVENC transcoding, please do not use unless you have a reason to.
dd status=progress if="$dvd_drive_path" of="$dvd_raw_path/$dvd_file_name.iso"
#HandBrakeCLI -i "$dvd_drive_path" --main-feature --preset 'H.265 NVENC 1080p' --output "$dvd_file_name_file_ext_with_path" >> "$log_file_handbrake"
echo "ssh 192.168.168.170 HandBrakeCLI -i \"$dvd_raw_path/$dvd_file_name.iso\" --main-feature --preset \"H.265\\ NVENC\\ 1080p\" --output \"$dvd_file_name_file_ext_with_path\" >> \"$log_file_handbrake\""
ssh 192.168.168.170 HandBrakeCLI -i "$dvd_raw_path/$dvd_file_name.iso" --main-feature --preset "H.265\ NVENC\ 1080p" --output "$dvd_file_name_file_ext_with_path" >> "$log_file_handbrake"
rm "$dvd_raw_path/$dvd_file_name.iso"
# CPU transcoding, please just use this.
#ssh 192.168.168.170 HandBrakeCLI -i "$dvd_drive_path" --main-feature --preset 'Creator 1080p60' --output "$dvd_file_name_file_ext_with_path" >> "$log_file_handbrake"
cd "$dash_vod_folder/"

# H264, suggested safe default to support all modern devices
#ssh 192.168.168.170 ffmpeg -y -vsync 0 -i "$dvd_file_name_file_ext_with_path" -c:v h264 -c:a aac -f dash "$dash_vod_folder/watch.mpd" >> "$log_file_ffmpeg" && sleep 10 && eject "$dvd_drive_path"
# H265, please be aware not all devices support H265
#ssh 192.168.168.170 ffmpeg -y -vsync 0 -i "$dvd_file_name_file_ext_with_path" -c:v h265 -c:a aac -f dash "$dash_vod_folder/watch.mpd" >> "$log_file_ffmpeg" && sleep 10 && eject "$dvd_drive_path"
# HEVC? Use H265, please. Same thing, probably, idk.
#ssh 192.168.168.170 ffmpeg -y -vsync 0 -i "$dvd_file_name_file_ext_with_path" -c:v hevc -c:a aac -f dash "$dash_vod_folder/watch.mpd" >> "$log_file_ffmpeg" && sleep 10 && eject "$dvd_drive_path"
# H264_NVENC, Nvidia-accelerated transcoding. Ignore and use above options if using no GPU!
echo "ssh 192.168.168.170 ffmpeg -y -vsync 0 -i \"$dvd_file_name_file_ext_with_path\" -c:v h264_nvenc -c:a aac -f dash \"$dash_vod_folder/watch.mpd\" >> \"$log_file_ffmpeg\" && sleep 10 && eject \"$dvd_drive_path\""
ssh 192.168.168.170 ffmpeg -y -vsync 0 -i "$dvd_file_name_file_ext_with_path" -c:v h264_nvenc -c:a aac -f dash "$dash_vod_folder/watch.mpd" >> "$log_file_ffmpeg" && sleep 10 && eject "$dvd_drive_path"
read temp
# H265_NVENC, Nvidia-accelerated transcoding. Ignore and use above options if using no GPU!
#ssh 192.168.168.170 ffmpeg -y -vsync 0 -i "$dvd_file_name_file_ext_with_path" -c:v h265_nvenc -c:a aac -f dash "$dash_vod_folder/watch.mpd" >> "$log_file_ffmpeg" && sleep 10 && eject "$dvd_drive_path"
# HEVC_NVENC, Nvidia-accelerated transcoding. Ignore and use above options if using no GPU!
#ssh 192.168.168.170 ffmpeg -y -vsync 0 -i "$dvd_file_name_file_ext_with_path" -c:v hevc_nvenc -c:a aac -f dash "$dash_vod_folder/watch.mpd" >> "$log_file_ffmpeg" && sleep 10 && eject "$dvd_drive_path"
# zerolatency flag doesn't work, not sure why. ignore! perhaps worked in the past.
#ssh 192.168.168.170 ffmpeg -y -vsync 0 -i "$dvd_file_name_file_ext_with_path" -c:v h264_nvenc -c:a aac -tune zerolatency -f dash "$dash_vod_folder/watch.mpd" >> "$log_file_ffmpeg" && sleep 10 && eject "$dvd_drive_path"
