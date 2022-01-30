#!/bin/bash

cd files/ldap/

sudo cp slapd.service /usr/lib/systemd/system/slapd.service

sudo systemctl enable slapd

