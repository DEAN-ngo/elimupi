#!/bin/sh
/usr/sbin/useradd $1 -M -g teachers -p $(openssl passwd -1 $2) -d /home/pi
