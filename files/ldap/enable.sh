#!/bin/bash

sudo cp slapd.service /usr/lib/systemd/system/slapd.service

sudo systemctl enable slapd

