#!/bin/bash

mygroups=$(/usr/bin/groups $2)
groups=$(/usr/bin/groups $1)

if [[ $groups =~ "sudo" ]]; then
	echo "Not allowed"
	exit 1
elif [[ $mygroups =~ " sudo" || $mygroups =~ " admins" ]] && [[ $groups =~ " students" || $groups =~ " teachers" ]]; then 
	/usr/sbin/userdel $1 
elif [[ $mygroups =~ " teachers" && $groups =~ " students" ]]; then
	/usr/sbin/userdel $1
else
	echo "Not allowed"
	exit 1
fi
