#!/bin/bash
#set -e
search_str=$(sed 's/ /+/g' <<< "$1")
curl_exec=$(curl -s "https://www.themoviedb.org/search?query=$search_str")
#echo "$curl_exec"
initial_stage=$(echo -n "$curl_exec" | grep -A8 '<div class="overview">' | grep -A1 '<p>' | head -n1 | cut -f2 -d'>' | cut -f1 -d'<')
echo "$initial_stage"
#final_stage=$(curl -s "https://www.themoviedb.org$initial_stage" | grep -A4 "<div class=\"blurred\" style=\"background-image: url('https://media.themoviedb.org/t/p/" | grep '<img class="poster w-full" src="https://media.themoviedb.org/t/p/' | cut -f4 -d'"')
