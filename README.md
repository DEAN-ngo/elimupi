# elimupi2.0.1

The purpose of this project is to provide a DEAN digital classroom environment for educational project in Tanzania and Kenia. This includes providing full Android tablet support without any direct internet connectivity. 

Please visit https://www.dean.ngo/ict4e/digital-classroom-box-elimupi/ for more information 

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
1. Install a base Raspberry Pi OS image on an SD card and create an empty file called 'ssh' in the root of the *boot* partition. This will enable SSH access to the Raspberry Pi. After you finished previous steps insert the SD card into the Raspberry Pi

2. Connect the external Hard Drive to the Raspberry Pi

3. Powerup the RaspberryPi and wait for the initial boot process to complete 

4. Connect with the Raspberry pi using SSH (`ssh pi@<ipv4>`; default password: raspberry)
 
5. Expand your microSD card partition by running `sudo raspi-config`, choose Advanced Options and there choose Expand Filesystem. Exit the menu (press the escape key a few times) and restart the Raspberry Pi: `sudo reboot`

6. After reboot, connect through SSH again and paste in the following command: 

`wget https://raw.githubusercontent.com/DEANpeterV/elimupi2.0/master/ElimuPi_installer.py && chmod 700 ElimuPi_installer.py && python3 ElimuPi_installer.py`

*Please note that this will change the 'pi' user's password to: elimupi 

7. After the reboot, connect to the Raspberry Pi through SSH again and continue the installation by running `python3 ElimuPi_installer.py` again. 

8. After another reboot, the Raspberry Pi is ready for use! 

All default username and passwords will be elimupi/elimupi unless noted differently. 

The default password for the wifi 'elimu' is : 1234567890

The end users can access the available resources after they are connected to the Wifi network.

The following links are provided to access the resources:
- Khan Academy - http://www.khan.local
- Kiwix - http://www.wiki.local
- Moodle: http://www.moodle.local
- files - http://www.files.local

After you finished the installation you need to visit khan.local and create an Admin account. 

Then you need to login with the Admin account and go to Manage --> "Please follow the directions to register your device, so that it can synchronize with the central server."


*NOTE1: This install is tested to work with 2018-11-13-raspbian-stretch-lite 

*NOTE2: for WIFI to work on the RaspberryPi 2 unit, you must have the WIFI USB dongle inserted during installation so that the install script can configure it properly. RaspberryPi 3 models have on board WiFi and don't need a WIFI USB dongle. 

*NOTE3: If using  PUTTY set the setting 'window'->'translation'->'Remote Character Set' to 'use font encoding' to display the lines correctly.

Last updated : 2021/05/24 

Applicable Licenses: See licenses file. 

Original source : https://github.com/rachelproject/rachelpios 
