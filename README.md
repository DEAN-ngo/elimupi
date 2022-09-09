# elimupi3.0.0

The purpose of this project is to provide a DEAN digital classroom environment for educational project in Tanzania and Kenia. This includes providing full Android tablet support without any direct internet connectivity. 

Please visit https://www.dean.ngo/ict4e/digital-classroom-box-elimupi/ for more information 

##Introduction 

This version of the ElimuPi build supports: 

- FDROID Android application store 
- Web pages for management of the sytem and content management (WordPress) 
- WiKiPedia alike functions (KiWix) 
- Kolibri learning system
- Dynamic content addition through USB storage 
- Secured WiFi access point 
- Local DHCP, DNS 

# Installation

## Prerequisites:
 - Download Raspberry Pi Imager https://www.raspberrypi.com/software/
- Start the Pi Imager and choose the operating system to install. Select ‘RASPBERRY PI OS LITE (64-BIT)’ and select the device to write the image to. Press ‘WRITE’ button. This will start downloading the image and install the software on the SD-card
 - ansible 2.10 > installed on laptop
 - ssh_askpass installed.
 - local pi.
 - Get ansible vault password from someone :-)

## How to run
Run ansible-play book against local raspberry pi:

 - install Ansible collections:

` ansible-galaxy collection install -r collections.yml`

 - install Ansible roles

`ansible-galaxy install -r roles.yml`

- run playbook

`ansible-playbook -i ./inventory.yml playbook-raspberrypi.yml --ask-vault-pass`

## After installation
After installing the software, follow the next step to connect to the Pi.
-	Connect your wifi to the ‘elimu’ network - passcode ‘1234567890’.
-	Start your browser
-	Open the website ‘start.elimupi.online

## How to update something in secrets.yml

`ansible-vault edit group_vars/all/secrets.yml`  

## Default users
All default username and passwords will be pi/elimupi unless noted differently. 

The default password for the WiFi 'elimu' is : 1234567890

##User access
The end users can access the available resources after they are connected to the Wifi network.

The following links are provided to access the resources:

- **Main interface** - start.elimupi.online
- **Kolibri** - kolibri.elimupi.online
- **Kiwix** - wiki.elimupi.online
- **Fdroid** - fdroid.elimupi.online
- **files** - files.elimupi.online
- **Moodle** - moodle.elimupi.online
- **Admin** - admin.elimupi.online

After you finished the installation you need to visit khan.local and create an Admin account. 

Then you need to login with the Admin account and go to Manage --> "Please follow the directions to register your device, so that it can synchronize with the central server."


##Notes
**NOTE1**: This install is tested to work with Raspbian OS #1414 SMP Fri Apr 30 13:20:47 BST 2021

**NOTE2*: for WIFI to work on the RaspberryPi 2 unit, you must have the WIFI USB dongle inserted during installation so that the install script can configure it properly. RaspberryPi 3 models have on board WiFi and don't need a WIFI USB dongle. 

**NOTE3**: If using  PUTTY set the setting 'window'->'translation'->'Remote Character Set' to 'use font encoding' to display the lines correctly.

**NOTE4**: For Kolibri content use Kolibri Studio - https://studio.learningequality.org

Last updated : 2022/09/09 

Applicable Licenses: See licenses file. 

Original source : https://github.com/rachelproject/rachelpios 
