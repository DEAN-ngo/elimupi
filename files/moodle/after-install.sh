#!/bin/bash

curl -s -f -o /dev/null "http://elimupi.local/admin/index.php?cache=0&lang=en&agreelicense=1"

curl -s -f -o /dev/null "http://elimupi.local/admin/index.php?cache=0&agreelicense=1&confirmrelease=1&lang=en"

# Install populated db (Admin passwod: Elimupi#1)
sudo mysql -p moodle < installed.sql

sudo moosh -p /var/moodle config-plugin-import auth_ldap.xml

sudo moosh -p /var/moodle cache-clear

sudo moosh -p /var/moodle auth-manage enable ldap
