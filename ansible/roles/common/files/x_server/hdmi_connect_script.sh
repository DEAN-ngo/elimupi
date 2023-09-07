#!/bin/bash

# Check the status of HDMI port 1
status_port1=$(/usr/bin/cat /sys/class/drm/card1/card1-HDMI-A-1/status)

# Check the status of HDMI port 2
status_port2=$(/usr/bin/cat /sys/class/drm/card1/card1-HDMI-A-2/status)

# Check if either HDMI port is connected
if [[ "$status_port1" == "connected" || "$status_port2" == "connected" ]]; then
        /usr/bin/systemctl start start-xserver.service
else
        /usr/bin/systemctl stop start-xserver.service
fi
