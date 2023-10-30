#!/bin/bash

# We check against the Google DNS for internet connection, if there is no
# connection, using Dnsmasq we resolve all the domains to the Elimupi address

# Check against the google DNS
ping -c 1 8.8.8.8 > /dev/null 2>&1

if [ $? -ne 0 ]; then
    # When the internet connection is down, forward everything to the elimupi address
    if [ ! -e /etc/dnsmasq.d/custom-dns.conf ]; then
      echo "Adding local forward"
      echo -e "address=/#/{{ host_ipv4_ipaddress }}\nlocal-ttl=5" > /etc/dnsmasq.d/custom-dns.conf
      systemctl restart dnsmasq
      systemctl restart systemd-resolved
    fi
else
    # We are available, remove the rule
    if [ -e /etc/dnsmasq.d/custom-dns.conf ]; then
      echo "Removing local forward"
      rm /etc/dnsmasq.d/custom-dns.conf
      systemctl restart dnsmasq
      systemctl restart systemd-resolved
    fi
fi
