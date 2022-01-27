#!/bin/bash

# Install populated db (Admin passwod: Elimupi#1)
sudo mysql -p moodle < installed.sql

sudo moosh -p /var/moodle config-plugin-import auth_ldap.xml

sudo moosh -p /var/moodle cache-clear

sudo moosh -p /var/moodle auth-manage enable ldap
