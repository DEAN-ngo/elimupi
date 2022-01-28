#!/bin/bash

sudo groupadd ldap
sudo useradd  -c "OpenLDAP Daemon Owner" \
         -d /var/lib/openldap \
         -g ldap -s /bin/false ldap

cd /tmp
mkdir openldap
cd /tmp/openldap
wget https://mirror.lyrahosting.com/OpenLDAP/openldap-release/openldap-2.6.1.tgz
gunzip openldap-2.6.1.tgz
tar xvf openldap-2.6.1.tar
cd openldap-2.6.1/

./configure

make depend
make

sudo make install

install -v -dm700 -o ldap -g ldap /usr/local/var/openldap-data
install -v -dm700 -o ldap -g ldap /usr/local/etc/openldap/slapd.ldif

rm -Rf /tmp/openldap
