# elimupi2.0

 This is a fork of the Rachel image builder adapted for DEAN. 

Purpose is to provide a classroom environment for educational project in Tanzania and Kenia. Its purpose it to provide full Android tablet support without any direct internet connectivity. 

Please visit http://www.dean.ngo/ict4e/digital-classroom-box-elimupi/ for more information 

Introduction 

THIS IS WORK IN PROGRESS: DO NOT USE!!!! -- All code will be ported to Python -- 

Functions 

This version of the ElimuPi build supports: 

FDROID Android application store 

Web pages for management of the sytem and content management (WordPress) 

WiKiPedia alike functions (KiWix) 

Khan Acadamy learning content 

Local Mail and calender functionality (Citadel) 

Dynamic content addition through USB storage 

Optional Secured WiFi access point 

Optional Local DHCP, DNS 

Installation 

Install a base RaspBian image on a SD card and insert it into the RaspberryPi. 

Create a empty file called 'ssh' in the root of the boot partition to enable SSH access to the Raspberry Pi 

Powerup the RaspberryPi and wait for the initial boot process to complete 

Logon with user pi (password: raspberry) 

Expand your microSD card partition sudo raspi-config sudo reboot 

paste in the following command after reboot. 

wget https://raw.githubusercontent.com/elimupi/elimupi2.0/master/ElimuPi_installer.py && chmod 700 ElimuPi_installer.py && python ElimuPi_installer.py 

Please note that this will change the 'pi' user's password to: elimupi 

All default username and passwords will be elimupi/elimupi unless noted differently. 

*NOTE1: This install is tested to work with 2017-09-07-raspbian-stretch-lite and 2018-03-13-raspbian-stretch-lite 

*NOTE2: for WIFI to work on the RaspberryPi 2 unit, you must have the WIFI USB dongle inserted during installation so that the install script can configure it properly. RaspberryPi 3 models have on board WiFi and don't need a WIFI USB dongle. 

Last updated : 2018/04/06 

Applicable Licenses: See licenses file. 

Original source : https://github.com/rachelproject/rachelpios 

 
