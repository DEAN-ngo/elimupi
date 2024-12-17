#!/bin/bash

if test -f client.ovpn; 
then
    /bin/cp client.ovpn /etc/openvpn/.
fi
if test -f dnsmasq.conf;
then
    /bin/cp /etc/dnsmasq.conf /etc/dnsmasq.conf.bak
    /bin/cp  dnsmasq.conf /etc/dnsmasq.conf
fi