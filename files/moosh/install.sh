#!/bin/bash

cd /var/
sudo mkdir moosh
cd moosh
wget https://moodle.org/plugins/download.php/25565/moosh_moodle311_2021113000.zip
unzip moosh_moodle311_2021113000.zip
sudo ln -s /var/mooshh/moosh.php /usr/local/bin/moosh
