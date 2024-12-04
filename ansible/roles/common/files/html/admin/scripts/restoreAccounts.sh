#!/bin/bash

dir="/home/pi/oldaccounts"

mkdir -p $dir


cp /etc/passwd /etc/shadow /etc/group /etc/gshadow "$dir"

if [ -f "$1" ] ; then
tar -zxvf $1 "/tmp"
else
echo "No backup file given"
fi

if [[ -f "/tmp/home/pi/backup/passwd.mig" ]] ; then
cat /tmp/home/pi/backup/passwd.mig >> /etc/passwd
cat /tmp/home/pi/backup/group.mig >> /etc/group
cat /tmp/home/pi/backup/shadow.mig >> /etc/shadow
/bin/cp /tmp/home/pi/backup/gshadow.mig >> /etc/gshadow
echo "Accounts restored. You need to reboot the pi"

else
echo "Something went wrong"

fi


