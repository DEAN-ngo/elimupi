#!/usr/bin/env sh
cd /var/www/leaphy/backend || exit
. /var/www/leaphy/backend/venv/bin/activate

export ARDUINO_DIRECTORIES_USER="/mnt/content/leaphy/user"
export ARDUINO_DIRECTORIES_DATA="/mnt/content/leaphy/data"
export ARDUINO_DIRECTORIES_DOWNLOADS="/mnt/content/leaphy/tmp"

exec python3 -m uvicorn --host 127.0.0.1 --port 8001 main:app
