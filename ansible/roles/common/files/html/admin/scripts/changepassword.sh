#!/bin/bash

groups=$(/usr/bin/groups $1)

if [[ $groups =~ " sudo" || $groups =~ " admins" ]]; then
	echo "Not allowed"
	exit 1
elif [[ $groups =~ " students" || $groups =~ " teachers" ]]; then 
	/usr/bin/passwd $1 
else
	echo "Not allowed"
	exit 1
fi
