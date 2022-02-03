#!/bin/bash

cd /mnt/content/fdroid
fdroid init
sed -i 's@^repo_url.*@repo_url = "http://fdroid.local"@' config.py
sed -i 's/repo_name.*/repo_name = "Elimupi App repository"/' config.py