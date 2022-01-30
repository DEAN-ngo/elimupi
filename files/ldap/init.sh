#!/bin/bash

cd files/ldap

# Create config database directory
if [ ! -d /usr/local/etc/openldap/slapd.d ];
then
	sudo mkdir /usr/local/etc/openldap/slapd.d
fi

# Create database
if [ ! -d  /usr/local/var/openldap-data ];
then
	sudo mkdir /usr/local/var/openldap-data
fi

# Populate the first
sudo slapadd -n 0 -l /usr/local/etc/openldap/slapd.ldif -F /usr/local/etc/openldap/slapd.d

# Add ldifs for objectClass = inetOrgPerson
sudo slapadd -n 0 -l /usr/local//etc/openldap/schema/cosine.ldif

sudo slapadd -n 0 -l /usr/local//etc/openldap/schema/inetorgperson.ldif

sudo slapmodify -n 0 -l suffix.ldif

sudo slapmodify -n 0 -l resetpassword.ldif

sudo service slapd start

ldapadd -f elimu.ldif -x -D "cn=Manager,dc=elimupi,dc=local" -w elimupi
