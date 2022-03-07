#!/bin/bash

echo -n "Copying sdcard image to volume sd ... "
cp /*-raspios-bullseye-armhf-lite.img /sd/sdcard.img
echo "DONE"

echo -n "Resizing sdcard image to 4G ... "
qemu-img resize -f raw /sd/sdcard.img 4G

echo -n "Copying kernel and Broadcom 2710 firmware to volume sd/kernel ... "
mkdir /sd/kernel
cp /kernel8.img bcm2710-rpi-3-b-plus.dtb /sd/kernel
echo "DONE"

exit 0
