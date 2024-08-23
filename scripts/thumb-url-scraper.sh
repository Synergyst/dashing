#!/bin/bash
#set -e
search_str=$(sed 's/ /+/g' <<< "$1")
curl_exec=$(curl -s "https://www.themoviedb.org/search?query=$search_str")
#echo "$curl_exec"
initial_stage=$(echo -n "$curl_exec" | grep -A8 '<div class="search_results movie ">' | grep 'class="result" href="/' | awk '{ print $6 }' | cut -f2 -d'"')
#echo "$initial_stage"
final_stage=$(curl -s "https://www.themoviedb.org$initial_stage" | grep -A4 "<div class=\"blurred\" style=\"background-image: url('https://media.themoviedb.org/t/p/" | grep '<img class="poster w-full" src="https://media.themoviedb.org/t/p/' | cut -f4 -d'"')
if [[ -n "$final_stage" ]]; then
  ffmpeg -hide_banner -loglevel error -y -i "$final_stage" -vf 'scale=139:239' "$2/cover.png"
  echo -n "$2/cover.png"
else
  echo -n 'http://watch.exp.lan/favicon.png'
fi
