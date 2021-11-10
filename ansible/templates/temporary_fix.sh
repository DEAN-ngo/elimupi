#!/bin/bash
# Get Kiwix script
sudo wget https://raw.githubusercontent.com/elimupi/elimupi2.0/master/files/kiwix-start.pl
# Get Kiwix code
sudo wget https://ftp.nluug.nl/pub/kiwix/release/kiwix-tools/kiwix-tools_linux-armhf-0.9.0.tar.gz

# Get kiwix service
sudo wget https://raw.githubusercontent.com/elimupi/elimupi2.0/master/files/kiwix-service
sudo tar xvzf kiwix-tools_linux-armhf-0.9.0.tar.gz
sudo mkdir -p /var/kiwix/bin
sudo cp /home/pi/kiwix-tools_linux-armhf-0.9.0/kiwix-manage /var/kiwix/bin/
sudo cp /home/pi/kiwix-tools_linux-armhf-0.9.0/kiwix-read /var/kiwix/bin/
sudo cp /home/pi/kiwix-tools_linux-armhf-0.9.0/kiwix-serve /var/kiwix/bin/
sudo cp /home/pi/kiwix-tools_linux-armhf-0.9.0/kiwix-search /var/kiwix/bin/
sudo cp /home/pi/kiwix-start.pl /var/kiwix/bin/kiwix-start.pl
sudo cp /home/pi/kiwix-service /etc/init.d/kiwix
sudo chmod +x /var/kiwix/bin/kiwix-start.pl
sudo chmod +x /etc/init.d/kiwix
sudo update-rc.d kiwix defaults
sudo systemctl daemon-reload
#sudo systemctl start kiwix
#sudo systemctl enable kiwix
