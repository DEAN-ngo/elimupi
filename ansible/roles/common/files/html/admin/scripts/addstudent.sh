#!/bin/sh
/usr/sbin/useradd $1 -M -g students  -p $(openssl passwd -1 $2) -d /home/pi

