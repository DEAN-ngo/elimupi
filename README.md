## ElimuPi

The purpose of this project is to provide a DEAN digital classroom environment for educational project in Tanzania and Kenya. This includes providing full Android tablet support without any direct internet connectivity.

Please visit https://www.dean.ngo/solutions/elimupi/  for more information.

## Introduction

This version of the ElimuPi build supports:

- [F-Droid](https://f-droid.org/) an Android application store.
- Web pages for management of the system and content management (WordPress).
- WiKiPedia alike functions based on [Kiwix](https://www.kiwix.org/en/about/).
- [Kolibri](https://learningequality.org/kolibri/) learning system.
- Dynamic content addition through USB storage.
- Secured WiFi Access Point.
- Local DHCP, DNS.

#  Installation

## Prerequisites:
 -  A Raspberry Pi model 4 with a wired TCP connection to LAN.
 -  32GB micro SD-card class 10.
 -  Ansible 2.10 > installed.
 - ssh_askpass installed.
-  [Raspberry Pi Imager](https://www.raspberrypi.com/software/) installed.
 - Insert the SD-card and start the Raspberry Pi Imager and use;  [Raspberry Pi OS Lite 64 bits image , Release date: September 6th 2022.](https://downloads.raspberrypi.org/raspios_lite_arm64/images/raspios_lite_arm64-2022-09-07/2022-09-06-raspios-bullseye-arm64-lite.img.xz) Select ‘RASPBERRY PI OS LITE (64-BIT)’ and select the device to write the image to. 
Select advanced option in the Raspberry Pi Imager -->vEnable SSH, Use password authentication. Set Username/password to : **pi/elimupi**
 Press ‘WRITE’ button. This will start downloading the image and install the software on the SD-card.
 - When the image has been successfully written insert the SD-card into the Raspberry Pi and boot the Pi.
 -  Find the Pi's assigned IP address on your LAN. 
 - Get the Ansible vault password from DEAN development to start the ElimuPI image provisioning via a Ansible playbook run.
 
## How to run
Run ansible-play book against local raspberry pi:

 - install Ansible collections:

` ansible-galaxy collection install -r collections.yml`

 - install Ansible roles

`ansible-galaxy install -r roles.yml`

 - adjust ansible_host: to the Pi's assigned IP address on your LAN in inventory.yml.

- run playbook

`ansible-playbook -i ./inventory.yml playbook-raspberrypi.yml --ask-vault-pass`

## After installation
After installing the software, follow the next step to connect to the Pi.
-       Connect your wifi to the ‘elimu’ network - passcode ‘1234567890’.
-      Point your browser to http://start.elimupi.online

## How to update something in secrets.yml

`ansible-vault edit group_vars/all/secrets.yml`

## Default users
All default username and passwords will be **pi/elimupi** unless noted differently.

The default password for the Wifi AP with SSID: 'elimu' is : 1234567890

## User access
The end users can access the available resources after they are connected to the Wifi network.

The following links are provided to access the resources:

- **Main interface** - [start.elimupi.online](http://start.elimupi.online)
- **Kolibri** - [kolibri.elimupi.online](http://kolibri.elimupi.online)
- **Kiwix** - [wiki.elimupi.online](http://wiki.elimupi.online)
- **Fdroid** - [fdroid.elimupi.online](http://fdroid.elimupi.online)
- **Files** - [files.elimupi.online](http://files.elimupi.online)
- **Moodle** - [moodle.elimupi.online](http://moodle.elimupi.online)
- **Admin** - [admin.elimupi.online](http://admin.elimupi.online)

After you finished the installation you need to visit [admin.elimupi.online](http://admin.elimupi.online) login with the Admin account and go to Manage --> "Please follow the directions to register your device, so that it can synchronize with the central server."

## Notes
**NOTE1**: This install is tested to work with [Raspberry Pi OS Lite 64 bits image , Release date: September 6th 2022.](https://downloads.raspberrypi.org/raspios_lite_arm64/images/raspios_lite_arm64-2022-09-07/2022-09-06-raspios-bullseye-arm64-lite.img.xz) on a Raspberry Pi model 4.

**NOTE2**: For Kolibri content use Kolibri Studio - https://studio.learningequality.org

Last updated : 2022/09/12

Applicable Licenses: See [licenses file](https://github.com/DEAN-ngo/elimupi/blob/main/LICENSE).
