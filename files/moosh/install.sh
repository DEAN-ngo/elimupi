#!/bin/bash

cd /var/
sudo wget https://moodle.org/plugins/download.php/25565/moosh_moodle311_2021113000.zip
sudo unzip moosh_moodle311_2021113000.zip
sudo ln -s /var/moosh/moosh.php /usr/local/bin/moosh
rm -f moosh_moodle311_2021113000.zip
