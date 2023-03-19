#!/bin/bash

# This scripts downloads all the videos from the provided JSON file using
# ffmpeg, please keep updated roles/common/files/scratch_gui/videos.json

# Check for input file
if [[ -z "$1" ]]; then
	echo "No input file specified"
	exit 1
fi

mkdir -p /opt/scratch-gui/static/tutorial_videos/

# Parse the JSON file and extract the values
for FILE_ID in $(jq -r '.[][]' "$1"); do
	ffmpeg -y -i http://fast.wistia.net/embed/medias/$FILE_ID.m3u8 -c copy /opt/scratch-gui/static/tutorial_videos/$FILE_ID.mp4
done

touch /tmp/videos_downloaded
