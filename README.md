# elimupi2.0

 This is a fork of the Rachel image builder adapted for DEAN. 

Purpose is to provide a classroom environment for educational project in Tanzania and Kenia. Its purpose it to provide full Android tablet support without any direct internet connectivity. 

Please visit http://www.dean.ngo/ict4e/digital-classroom-box-elimupi/ for more information 

Introduction 

THIS IS WORK IN PROGRESS: DO NOT USE!!!! -- All code will be ported to Python -- 

Functions 

This version of the ElimuPi build supports: 

- FDROID Android application store 
- Web pages for management of the sytem and content management (WordPress) 
- WiKiPedia alike functions (KiWix) 
- Khan Acadamy learning content 
- Dynamic content addition through USB storage 
- Secured WiFi access point 
- Local DHCP, DNS 

Installation 
1. Install a base RaspBian image on a SD card and create a empty file called 'ssh' in the root of the boot partition. This will enable SSH access to the Raspberry Pi. After you finished previous steps insert the SD card into the Raspberry Pi

2. Connect the external Hard Drive to the Raspberry Pi

3. Powerup the RaspberryPi and wait for the initial boot process to complete 

4. Connect with the Raspberry pi using SSH (ssh pi@<ipv4>; default password: raspberry)
 
5. Expand your microSD card partition sudo raspi-config sudo reboot 

6. Paste in the following command after reboot. 

wget https://raw.githubusercontent.com/elimupi/elimupi2.0/master/ElimuPi_installer.py && chmod 700 ElimuPi_installer.py && python ElimuPi_installer.py

*Please note that this will change the 'pi' user's password to: elimupi 

7. After the reboot the installation will continue

All default username and passwords will be elimupi/elimupi unless noted differently. 

The default password for the wifi 'elimu' is : 1234567890

The end users can access the available resources after they are connected to the Wifi network.

The following links are provided to access the resources:
- Khan Academy - khan.local
- Kiwix - wiki.local
- files - files.local

*NOTE1: This install is tested to work with 2018-11-13-raspbian-stretch-lite 

*NOTE2: for WIFI to work on the RaspberryPi 2 unit, you must have the WIFI USB dongle inserted during installation so that the install script can configure it properly. RaspberryPi 3 models have on board WiFi and don't need a WIFI USB dongle. 

Last updated : 2018/11/26 

Applicable Licenses: See licenses file. 

Original source : https://github.com/rachelproject/rachelpios 
