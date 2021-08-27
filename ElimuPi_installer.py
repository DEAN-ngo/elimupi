#! /usr/bin/env python3
#coding=ASCII
# =========================================================================================================
#    Original script from Rachel
#    Modified for DEAN ElimuPI
#
#    Date        |By      | Description
# ---------------+--------+----------------------------------------
#    2017-Apr-3  | PVe    | Initial fork 
#    2017-Jun-28 | PVe    | Updated base configuration
#    2018-Feb-28 | PVe    | Added more modular design
#    2018-Mar-15 | PBo    | Bug fixes
#    2018-Mar-26 | PVe    | Updated files handling
#    2018-Apr-04 | PVe    | Updated file update mechanism
#    2018-Apr-25 | PVe    | Minor change, make STEM compilation optional
#    2018-May-16 | PVe    | Included STEM compilation steps to build
#    2018-Nov-26 | PVe    | Update installation process
#    2020-Dec-21 | PVe    | Added support for latest Raspberry OS and Moodle
#    2021-Jan-02 | PVe    | Updated to use curses GUI
#    2021-May-xx | xxx    | Various updates
#    2021-Aug-04 | Pve    | updated GUI and add Kolibri for 
#    2021-Aug-09 | Pve    | updated GUI and add Kolibri for
# =========================================================================================================

import sys
import os
import subprocess
import argparse
import shutil
import urllib.request
import argparse
import platform
import curses           #curses is the interface for capturing key presses on the menu, os launches the files
import xml.etree.ElementTree as ET
import json
import re
import crypt

from time import sleep
# from __builtins__ import true

# ================================
# Settings for build
# ================================
base_hostname       = "elimupi"                                         # Default host name
base_user           = "pi"                                              # Default user name to use
base_passwd         = "elimupi"                                         # Default password for all services
base_ip_range       = "10.11.0"                                         # IP range (/24) for the WiFI interface
base_ip             = "10.11.0.1"                                       # Default IP address for the WiFi interface
base_subnet         = "255.255.255.0"                                   # Base sub net
base_build          = "ELIMUPI-20210809-1"                              # Date of build
base_git            = "https://github.com/DEANpeterV/elimupi2.0.git"    # GIT location
base_wifi           = "wlan0"

# ================================
# Class for command execution
# ================================
class CmdResult:
    result = True
    error = ''
    output = ''

# ================================
# Current screen location
# ================================
class screenPos:
    row = 1
    col = 1

# ================================
# Command line arguments
# ================================
argparser = argparse.ArgumentParser()
# Switch to install Moodle
argparser.add_argument("--moodle",
                        dest="install_moodle",
                        action="store_false",
                        help="Install Moodle")
# Switch to disable WiFi
argparser.add_argument("--no-wifi",
                        dest="install_wifi",
                        action="store_false",
                        help="Do not configure local wifi hotspot.")
# --citadel switch to enable citadel
argparser.add_argument("--citadel",
                        dest="install_citadel",
                        action="store_true",
                        help="Install Citadel mail, chat and colaboration suite")
args = argparser.parse_args()

# ================================
# Init screen display
# ================================
screen = curses.initscr() #initializes a new window for capturing key presses
curses.noecho() # Disables automatic echoing of key presses (prevents program from input each key twice)
curses.cbreak() # Disables line buffering (runs each key as it is pressed rather than waiting for the return key to pressed)
curses.start_color() # Lets you use colors when highlighting selected menu option
screen.keypad(1) # Capture input from keypad

# ================================
# Change this to use different colors when highlighting
# ================================
curses.init_pair(1, curses.COLOR_YELLOW, curses.COLOR_RED)  # Sets up color pair #1
 
# ================================
# Define color pairs (max 8 colors used)
# ================================
curses.init_pair(1,curses.COLOR_WHITE, curses.COLOR_BLUE) # Sets up color pair winInfo
curses.init_pair(2,curses.COLOR_RED, curses.COLOR_BLUE) # Sets up color pair winInfo
curses.init_pair(3,curses.COLOR_GREEN, curses.COLOR_BLUE) # Sets up color pair winInfo

curses.init_pair(4,curses.COLOR_BLUE, curses.COLOR_WHITE) # Sets up color pair OK
curses.init_pair(5,curses.COLOR_RED , curses.COLOR_WHITE) # Sets up color pair Log
curses.init_pair(6,curses.COLOR_BLACK, curses.COLOR_WHITE) # Sets up color pair Log

col_info     = curses.color_pair(1)
col_info_err = curses.color_pair(2)
col_info_ok  = curses.color_pair(3)

col_log      = curses.color_pair(4)
col_log_err  = curses.color_pair(5)
col_log_ok   = curses.color_pair(6)

# ================================
# Window layout
# 0        .. 13         progressWin
# 15       .. end - 12   logWin
# end - 12 .. end        infoWin
# ================================
# Define status window 
# ================================
posx    = 4
posy    = 0
height  = 12
width   = curses.COLS - 8
statwin = curses.newwin(height, width , posy, posx)

# ================================
# Define log window
# ================================
logwin_scroll_area  =32767
logwin_width        = curses.COLS - 8
logwin              = curses.newpad(logwin_scroll_area, logwin_width)
logwin.scrollok(True)
logwin_dwidth       = curses.COLS - 4
logwin_dheight      = curses.LINES - 10
if logwin_dheight <= 0:
    logwin_dheight = 1
logwin_posx         = 4
logwin_posy         = 12
# Displays a section of the pad in the middle of the screen.
# (0,0) : coordinate of upper-left corner of pad area to display.
# (5,5) : coordinate of upper-left corner of window area to be filled
#         with pad content.
# (20, 75) : coordinate of lower-right corner of window area to be
#          : filled with pad content.
# pad.refresh( 0,0, 5,5, 20,75)
logwin.bkgd(' ', col_log)
logwin.addstr("Starting install", col_log)
logwin.refresh(0, 0, logwin_posy, logwin_posx, logwin_dheight, logwin_dwidth)


# ================================
# Define Info window
# ================================
height = 8
posy = curses.LINES - 9
infowin = curses.newwin(height, width , posy , posx) # 
infowin.bkgd(' ', col_info)
infowin.border(0)

def install_udev_handler():
    return True

# ================================
# Install USB mounter 
# ================================
def install_usbmount():
    result = sudo("apt-get install usbmount ntfs-3g -y","Unable to download usbmount")
    display_log(result.output)
    sudo("sed -i '/MountFlags=slave/c\MountFlags=shared' /lib/systemd/system/systemd-udevd.service","Unable to update udevd configuration (systemd-udevd.service)")
    sudo("chmod +x ./build_elimupi/files/01_create_label_symlink ./build_elimupi/files/01_remove_model_symlink", False)
    cp("./files/usbmount/usbmount.conf", "/etc/usbmount/")
    cp("./files/usbmount/01_create_label_symlink", "/etc/usbmount/mount.d/")
    cp("./files/usbmount/01_remove_model_symlink", "/etc/usbmount/umount.d")
    cp("./files/usbmount/usbmount@.service", "/etc/systemd/system/")
    cp("./files/usbmount/usbmount.rules", "/etc/udev/rules.d")
    display_log("usb mount installed")
    return True

# ================================
# Setup WiFi
# ================================
def install_wifi():
    # Set country for wpa_wpa_supplicant.conf
    display_log("Set country for WiFi")
    sudo("echo country=ke>>/etc/wpa_supplicant/wpa_supplicant.conf")
    
    sudo("rfkill unblock wifi", "Unable to unblock WiFi is not")
    sudo("ifconfig {} {}".format(base_wifi, base_ip), "Unable to set {} IP address {}".format(base_wifi, base_ip))
    
    #Install hostapd, udhcpd
    sudo("apt-get -y install hostapd udhcpd", "Unable install hostapd and udhcpd.")
    
    #copy config files udhcpd
    cp("./files/udhcpd.conf", "/etc/udhcpd.conf", "Unable to copy uDHCPd configuration (udhcpd.conf)")
    cp("./files/udhcpd", "/etc/default/udhcpd"  , "Unable to copy UDHCPd configuration (udhcpd)")

    #copy config files hostapd
    cp("./files/hostapd", "/etc/default/hostapd", "Unable to copy hostapd configuration (hostapd)")
    cp("./files/hostapd.conf", "/etc/hostapd/hostapd.conf", "Unable to copy hostapd configuration (hostapd.conf)")
    
    #change udhcpd file
    sudo("sed -i '/interface    wlan0/c\interface    {}' /etc/udhcpd.conf".format(base_wifi)) 
    sudo("sed -i '/start/c\start        " + base_ip_range + ".11    #default: 192.168.0.20\' /etc/udhcpd.conf", "Unable to update uDHCPd configuration (udhcpd.conf)") 
    sudo("sed -i '/end/c\end        " + base_ip_range + ".199    #default: 192.168.0.254\' /etc/udhcpd.conf", "Unable to update uDHCPd configuration (udhcpd.conf)")
    sudo("sed -i '/^option.*subnet/c\option    subnet    " + base_subnet + "' /etc/udhcpd.conf", "Unable to update uDHCPd configuration (udhcpd.conf)")
    sudo("sed -i '/^opt.*router/c\opt    router    " + base_ip + "' /etc/udhcpd.conf", "Unable to update uDHCPd configuration (udhcpd.conf)")

    #change hostapd file
    sudo("sed -i '/interface=wlan0/c\interface={}' /etc/hostapd/hostapd.conf".format(base_wifi)) 

    #change iptables
    #sudo("sh -c 'echo 1 > /proc/sys/net/ipv4/ip_forward'", "Unable to set ipv4 forwarding")
    #cp("./files/sysctl.conf", "/etc/sysctl.conf", "Unable to copy sysctl configuration (sysctl.conf)")
    #sudo("iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE", "Unable to set iptables MASQUERADE on eth0.")
    #sudo("iptables -A FORWARD -i eth0 -o {} -m state --state RELATED,ESTABLISHED -j ACCEPT".format(base_wifi), "Unable to forward wlan0 to eth0.")
    #sudo("iptables -A FORWARD -i {} -o eth0 -j ACCEPT".format(base_wifi), "Unable to forward wlan0 to eth0.")
    #sudo("sh -c 'iptables-save > /etc/iptables.ipv4.nat'", "Unable to save iptables configuration.")
    sudo("ifconfig {}".format(base_wifi, base_ip), "Unable to set wlan0 IP address (" + base_ip + ")")

    #start & enable hostapd, udhcpd
    sudo("systemctl unmask  hostapd", "Unable to unmask hostapd")
    sudo("systemctl start hostapd", "Unable to start hostapd service.")
    sudo("systemctl start udhcpd", "Unable to start udhcpd service.")
    sudo("update-rc.d hostapd enable", "Unable to enable hostapd on boot.")
    sudo("update-rc.d udhcpd enable", "Unable to enable UDHCPd on boot.")

    # udhcpd wasn't starting properly at boot (probably starting before interface was ready)
    # for now we we just force it to restart after setting the interface
    sudo("sh -c 'sed -i \"s/^exit 0//\" /etc/rc.local'", "Unable to remove exit from end of /etc/rc.local")
    sudo("sh -c 'echo rfkill unblock all >> /etc/rc.local; echo ifconfig {} {} >> /etc/rc.local; echo service udhcpd restart >> /etc/rc.local;'".format(base_wifi, base_ip), "Unable to setup udhcpd reset at boot.")
    sudo("sh -c 'echo exit 0 >> /etc/rc.local'", "Unable to replace exit to end of /etc/rc.local")
    # sudo("ifdown eth0 && ifdown wlan0 && ifup eth0 && ifup wlan0", "Unable to restart network interfaces.")
    return True

# ================================
# Install Moodle components
# ================================
def install_moodle():
    # quick install guide: https://docs.moodle.org/310/en/Installation_quick_guide
    # full install guide: https://docs.moodle.org/310/en/Installing_Moodle
    display_log("Install Moodle...")
    if exists("/mnt/"):
        content_prefix = "/mnt/content "
        content_prefix_escaped = "\/mnt\/content"
    else:
        sudo("mkdir --parents /var/moodlecontent")
        content_prefix = "/var/moodlecontent"
        content_prefix_escaped = "\/var\/moodlecontent"

    # Install MariaDB or =MySQL 
    display_log("Installing mariadb-server...", col_log_ok)
    sudo("apt-get install -y mariadb-server","Unable to install MariadbServer")
    display_log("Completed installing mariadb-server...", col_log_ok)
    
    # Determine last stable version (now fixed at 311)
    # sudo("sed -i 's//var\/run\/usbmount\/Content\/moodledb' /etc/mysql/mariadb.conf.d/nano 50-server.cnf","Unable to set mariadb folder")
    # Start and enable DBserver
    
    display_log("Configuring mariadb-server...", col_log_ok)
    sudo("systemctl enable mariadb.service","Unable to enable DB")
    sudo("systemctl restart mariadb.service","Unable to restart DB")
    # copy the sql files
    sudo("mkdir --parents /var/moodlesql", "Unable to create /var/moodlesql")
    cp("files/moodle/sql/01-create-database.sql", "/var/moodlesql", "Unable to copy 01-create-database.sql")
    cp("files/moodle/sql/02-create-user.sql", "/var/moodlesql", "Unable to copy 02-create-user.sql")
    # execute the required database commands
    sudo("cat /var/moodlesql/*.sql | sudo mysql")
    display_log("Completed configuring mariadb-server...", col_log_ok)
    
    # /etc/systemd/system/mysqld.service
    # LimitNOFILE=16384

    # create secure setup
    # Create/setup default user
    
    # Add PHP mySQL support (mariaDB)
    display_log("Installing PHP and tools...", col_log_ok)
    sudo("apt-get install php-fpm php-mysql php -y","Unable to install php-fpm, php-mysql or php")
    display_log("Completed installing PHP and tools", col_log_ok)
    # /etc/php/7.3/fpm/php.ini

    # Install PHP extensions
    display_log("Installing PHP extensions...", col_log_ok)
    sudo("apt-get install php-curl php-xml php-mbstring php-zip php-gd php-intl php-xmlrpc php-soap -y","Unable to install PHP extensions")
    display_log("Done installing PHP extensions", col_log_ok)

    # use /var/moodle for install
    sudo("rm --force --recursive /var/moodle")
    display_log("Downloading Moodle...", col_log_ok)
    sudo("git clone -b MOODLE_310_STABLE git://git.moodle.org/moodle.git /var/moodle", "Unable to clone Moodle")
    display_log("Done downloading Moodle", col_log_ok)
    # chown -R root /path/to/moodle
    sudo("chown --recursive root /var/moodle", "Unable to set moodle permissions")
    sudo("chmod --recursive 0755 /var/moodle", "Unable to set moodle permissions")
    
    # edit config.php to use mariadb
    #   $CFG->dbtype    = 'mariadb'; 
    #   $CFG->dblibrary = 'native';
    cp("/var/moodle/config-dist.php", "/var/moodle/config.php")
    # Configure Moodle
    sudo("sed --in-place \"s/\'pgsql\'\;/\'mariadb\'\;/\" /var/moodle/config.php", "Unable to update moodle configuration database(config.php)")
    sudo("sed --in-place \"s/\'username\'\;/\'elimu\'\;/\" /var/moodle/config.php", "Unable to update moodle configuration username (config.php)")
    sudo("sed --in-place \"s/\'password\'\;/\'elimu\'\;/\" /var/moodle/config.php", "Unable to update moodle configuration password (config.php)")
    sudo("sed --in-place 's/example.com\/moodle/www.moodle.local/' /var/moodle/config.php", "Unable to update moodle configuration url (config.php)")
    sudo("sed --in-place 's/\/home\/example\/moodledata/{}\/Content\/moodledata/' /var/moodle/config.php".format(content_prefix_escaped), "Unable to update moodle configuration content (config.php)")
    
    # create moodle data folder on content disk (!)
    sudo("mkdir --parents {}/Content/moodledata".format(content_prefix), "Unable to create Moodle folder")
    # Set default permissions
    sudo("chmod 0777 {}/Content/moodledata".format(content_prefix), "Unable to set rights on Moodle folder")
    # Set owner
    sudo("chown www-data {}/Content/moodledata".format(content_prefix), "Unable to set owner of Moodle folder")
    # chown www-data /path/to/moodle
    # cd /path/to/moodle/admin/cli

    # Copy moodle site settings
    cp("files/nginx/moodle.local", "/etc/nginx/sites-available/", "Unable to copy file moodle.local (nginx)")
    sudo("ln --symbolic --force /etc/nginx/sites-available/moodle.local /etc/nginx/sites-enabled/moodle.local", "Unable to copy file moodle.local (nginx)")
    
    # restart NGINX service 
    sudo("systemctl restart nginx", "Unable to restart nginx")
    
    # Setup cron job
    cp("files/moodle/cron.txt", "/var/moodlesql", "Unable to copy Moodle cron file")
    sudo("crontab -u www-data /var/moodlesql/cron.txt", "Unable to setup cron for Moodle")

    # We don't need to run the install script, as the config.php is already created above.
    # The local administrator should visit http://www.moodle.local first to setup the admin account, otherwise that account might be hijacked by students.
    # sudo("-u www-data /usr/bin/php /var/moodle/admin/cli/install.php", "Unable to install moodle")

    
    # see https://docs.moodle.org/310/en/Installing_Moodle
    #      https://docs.moodle.org/310/en/Administration_via_command_line#Installation
    # Create database (mariadb)
    
    # Moodle command line tools for Web Gui : https://moosh-online.com/commands/ 
    return True

# ================================
# Install web interface
# ================================
def install_web_interface():
    display_log("Install nginx...")
    sudo("apt-get install -y nginx","Unable to install NGINX")  #INSTALL NGINX
    display_log("Install PHP extensions...", col_log_ok)
    sudo("apt install php-fpm -y","Unable to install NGINX")
    
    # Install nginx site files
    display_log("Set site ingo...")  
    cp("./files/nginx/admin.local", "/etc/nginx/sites-available/", "Unable to copy file admin.local (nginx)")
    cp("./files/nginx/files.local", "/etc/nginx/sites-available/", "Unable to copy file files.local (nginx)")
    sudo("ln --symbolic --force /etc/nginx/sites-available/admin.local /etc/nginx/sites-enabled/admin.local", "Unable to copy file admin.local (nginx)")
    sudo("ln --symbolic --force /etc/nginx/sites-available/files.local /etc/nginx/sites-enabled/files.local", "Unable to copy file files.local (nginx)")
   
    # If folder doesn't exist create   
    if not os.path.exists('/var/www/log'):
        display_log("Create log folder...", col_log_ok)
        sudo("mkdir /var/www/log","Unable to create the NGINX log folder")
    
    # restart NGINX service 
    display_log("Restart nginx...", col_log_ok)
    sudo("systemctl restart nginx", "Unable to restart nginx")
    
    # TODO: !!Put content on public GIT or use username and password!!!!!
    # sudo ('git clone https://github.com/DEANpeterV/ElimuPi-Web-Interface.git') 
    # ' https://github.com/DEANpeterV/ElimuPi-Web-Interface/archive/main.zip
    
    # Copy the script in the admin/scripts folder to /usr/sbin/ as executable
    
    # Add Webinterface sudo settings
    display_log("Update sudo permissions...", col_log_ok)
    cp("./files/sudoers.d/020_elimupi", "/etc/sudoers.d/")    
    
    
    # Add base groups
    display_log("Add users andgroups...", col_log_ok)
    sudo("addgroup admins")
    sudo("addgroup teachers") 
    sudo("addgroup students")
    
    # Create two deanadmin account for system
    hashed_password = crypt.crypt("topsecret","salt")
    sudo("useradd -m -g admins -p \"" + hashed_password+ "\" deanadmin")
    
    # Create headmaster account for system
    hashed_password = crypt.crypt("headmaster","salt")
    sudo("useradd -m -g teachers -p \"" + hashed_password+ "\" headmaster")
    
    # add Pi to teachers group
    sudo("usermod -a -G teachers pi")
    display_log("Done setting up web interface...", col_log_ok)
    return True

# ================================
# Install Kolibri
# From https://kolibri.readthedocs.io/en/latest/install/raspberry_pi_manual.html
# ================================
def install_kolibri():
    display_log("Kolibri install support python libraries...")
    sudo("apt install libffi-dev python3-pip python3-pkg-resources dirmngr -y", "Unable to install Kolibri step 1")
    sudo("pip3 install pip setuptools --upgrade", "Unable to install setuptools")
    sudo("pip3 install cffi --upgrade","Unable to install cffi")
    
    display_log("Add http://ppa.launchpad.net/learningequality as installer source...", col_log_ok)
    sudo("su -c 'echo deb http://ppa.launchpad.net/learningequality/kolibri/ubuntu bionic main > /etc/apt/sources.list.d/learningequality-ubuntu-kolibri-bionic.list'")
    sudo("apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys DC5BAA93F9E4AE4F0411F97C74F88ADB3194DD81")
    sudo("apt update")
    
    display_log("Install Kolibri components...")
    sudo("apt install kolibri kolibri-server")
    display_log("Install Kolibri completed...", col_log_ok)
    return True
    
# ================================
# Kiwix install
# ================================
def install_kiwix():
    # Get release rss data from mirror
    display_log("Install KIWIX components...")
    display_log("Get KIWIX versions...", col_log_ok)
    file = urllib.request.urlopen('https://ftp.nluug.nl/pub/kiwix/release/kiwix-tools/feed.xml')
    data = file.read()
    file.close()
    # Parse XML
    root = ET.fromstring(data)
    # get latest version for linux-armhf of kiwix-tools (first in XML)
    latest_release = 'none'
    for links in root.findall('channel/item/link'):
            if( links.text.find('kiwix-tools_linux-armhf') > -1):
                    latest_release = links.text
                    break
    latest_release_name = latest_release[47:-7]
    display_log("Latest KIWIX release : " + latest_release_name, col_log_ok)
    
    # get release for Linux-armhf from mirror
    display_log("Downloading kiwix-tools...", col_log_ok)
    sudo("curl -s https://ftp.nluug.nl/pub/kiwix/release/kiwix-tools/" + latest_release_name + ".tar.gz | tar xz -C /home/pi/", "Unable to get latest kiwix release (https://ftp.nluug.nl/pub/kiwix/release/kiwix-tools/" + latest_release_name + ")")
    display_log("Completed downloading kiwix-tools", col_log_ok)
    # Make kiwix application folder
    sudo("mkdir --parents /var/kiwix/bin", "Unable to make create kiwix directories")
    # Copy files we need from the toolset
    cp("/home/pi/" + latest_release_name + "/kiwix-manage", "/var/kiwix/bin/", "Unable to copy kiwix-manage")
    cp("/home/pi/" + latest_release_name + "/kiwix-read", "/var/kiwix/bin/", "Unable to copy kiwix-read")
    cp("/home/pi/" + latest_release_name + "/kiwix-search", "/var/kiwix/bin/", "Unable to copy kiwix-search")
    cp("/home/pi/" + latest_release_name + "/kiwix-serve", "/var/kiwix/bin/", "Unable to copy kiwix-serve")
    # Copy config files
    cp("./files/kiwix/kiwix-start.pl", "/var/kiwix/bin/kiwix-start.pl", "Unable to copy dean-kiwix-start wrapper")
    sudo("chmod +x /var/kiwix/bin/kiwix-start.pl", "Unable to set permissions on dean-kiwix-start wrapper")
    cp("./files/kiwix/kiwix-service", "/etc/init.d/kiwix", "Unable to install kiwix service")
    sudo("chmod +x /etc/init.d/kiwix", "Unable to set permissions on kiwix service.")
    cp("./files/kiwix/kiwix.service", "/etc/systemd/system/kiwix.service", "Unable to copy kiwix systemd service file")

    def latest_zim_package(url, package):
        with urllib.request.urlopen(url) as response:
            html = response.read().decode('utf-8')
            versions = re.findall(package + "\d{4}-\d{2}.zim", html)
            versions.sort()
            #the last entry is the newest version
            return versions[-1]

    #download two sample wikis
    url_vikidia = "https://ftp.nluug.nl/pub/kiwix/zim/vikidia/"
    package_vikidia = latest_zim_package(url_vikidia, "vikidia_en_all_nopic_")
    display_log("Downloading {}...".format(package_vikidia), col_log_ok)
    sudo("curl --silent {}{} --output /var/kiwix/bin/{}".format(url_vikidia, package_vikidia, package_vikidia), "unable to download {}{}".format(url_vikidia, package_vikidia))
    display_log("Complered download sample zim 1", col_log_ok)

    url_wiktionary = "https://ftp.nluug.nl/pub/kiwix/zim/wiktionary/"
    package_wiktionary = latest_zim_package(url_wiktionary, "wiktionary_en_simple_all_nopic_")
    display_log("Downloading {}...".format(package_wiktionary), col_log_ok)
    sudo("curl --silent {}{} --output /var/kiwix/bin/{}".format(url_wiktionary, package_wiktionary, package_wiktionary), "unable to download {}{}".format(url_wiktionary, package_wiktionary))
    display_log("Complered download sample zim 2", col_log_ok)

    # Create service
    sudo("update-rc.d kiwix defaults", "Unable to register the kiwix service.")
    sudo("systemctl daemon-reload", "systemctl daemon reload failed")
    sudo("systemctl start kiwix", "Unable to start the kiwix service")
    sudo("systemctl enable kiwix", "Unable to enable the kiwix service")
    display_log("Kiwix service created", col_log_ok)
    # PBo 20180312-07 sudo("service kiwix start", "Unable to start the kiwix service.")
    #sudo("sh -c 'echo {} >/etc/kiwix-version'".format(kiwix_version), "Unable to record kiwix version.")
    # setup NGINX site
    cp("./files/nginx/wiki.local", "/etc/nginx/sites-available/", "Unable to copy file wiki.local (nginx)")
    # Enable site
    sudo("ln --symbolic --force /etc/nginx/sites-available/wiki.local /etc/nginx/sites-enabled/wiki.local", "Unable to enable file wiki.local (nginx)")
    # restart NGINX service 
    sudo("systemctl restart nginx", "Unable to restart nginx")
    return True

# ================================
# DNSMasq
# ================================
def install_dnsmasq():
    sudo("apt-get install dnsmasq -y", "Unable to install dnsmasq.")
    cp("./files/hosts", "/etc/hosts", "Unable to copy file hosts (/etc/hosts)")
    #sudo("rm /etc/dnsmasq.conf")
    return True

# ================================
# Setup Citadel mail suite
# https://dzone.com/articles/how-to-install-citadel-mail-server-on-ubuntu-1604-1
# Note ths installer gives a GUI to configure
# ================================
def install_citadel():
    sudo("apt-get install citadel-suite -y", "Unable to install Citadel")
    sudo("/usr/lib/citadel-server/setup", "Unable to setup Citadel")
    return True

# ================================
# Install fdroid environment
# ================================
def install_fdroid():
    # Install FDROID server components
    sudo("apt-get install fdroidserver -y", "Unable to install FDroid")
    # Enable shared folder for client access
    cp("./files/nginx/fdroid.local", "/etc/nginx/sites-available/", "Unable to copy file fdroid.local (nginx)")
    sudo("systemctl restart nginx", "Unable to restart nginx")
    return True

# ================================
# Check for content disk, if mounted continue
# If not mounted ask to attach, try to mount, check if correctly formatted and labeled 
# ================================
def setup_content_disk():
    # Get all current disks
    display_log("Get current disks\r\n")
    result = sudo("lsblk -Jno mountpoint,label","")
    disk_info = json.loads(result.output)
    for item in disk_info["blockdevices"]:
        if item["label"] is not None:
            # print("Label: " + item["label"])
            display_log("Label : " + str(item["label"]) + "\r\n")
    display_log("\r\n----------------------------------\r\n")
    sys.exit(0)
    return True

# ================================
# Configure content disk to mount at boot
# ================================
def add_content_disk_to_fstab():
    display_log("Add content disk to fstab" )
    with open("/etc/fstab") as fstab_readable:
        fstab_content = fstab_readable.read()
        if not "LABEL=Content" in fstab_content:
            sudo("echo 'LABEL=Content /mnt/content ntfs defaults,noatime,nofail 0 0' | sudo tee --append /etc/fstab", "Could not add content disk to /etc/fstab")
    display_log("Completed update of fstab")
    return True


def create_img():
    # use scripts to create an gzipped, compressed image file to distribute on the external disk
    sudo("mkdir /mnt/","Unable to create image folder")
    return True
# ================================
# Sudo 
# ================================
def sudo(s, error_msg = False):
    result = cmd("sudo DEBIAN_FRONTEND=noninteractive %s" % s)
    if error_msg and not result.result:
        die(error_msg, result)
    return result 

# ================================
# abort command (clean exit)
# ================================
def abort(msg):
    curses.endwin()
    print("Exit: " + str(msg))
    sys.exit(0)
    
# ================================
# die command (error exit)
# ================================
def die(msg, cmd_result=None):
    # End cusrses mode
    curses.endwin()
    
    # display error
    print("Error: " + str(msg))
    if cmd_result:
        print(cmd_result.error)
    sys.exit(1)

# ================================
# CMD command
# ================================
def cmd(run_cmd):
    # initialize result
    cmd_result=CmdResult()
    # Execute command, PY 2.4 - 2.6
    result = subprocess.Popen(run_cmd, shell=True, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE, close_fds=True)
    # result = subprocess.check_output(c, shell=True, stdin=subprocess.PIPE, stderr=subprocess.PIPE, close_fds=True)
    try:
        cmd_result.output, cmd_result.error = result.communicate() # PY 2.4 - 2.6
    except KeyboardInterrupt:
        pass
    cmd_result.result = (result.returncode == 0)
    #return (result.returncode == 0)
    return (cmd_result)

# ================================
# exists command
# ================================
def exists(p):
    return os.path.isfile(p) or os.path.isdir(p)

# ================================
# Copy command
# ================================    
def cp(source_file, destination_file, err_msg=False):
    if source_file.startswith("/"):
        sudo("cp {} {}".format(source_file, destination_file), err_msg)
    elif localinstaller():
        sudo("cp %s/%s %s" % (basedir(), source_file, destination_file), err_msg)
    else:
        sudo("cp %s/%s %s" % (basedir() + "/build_elimupi", source_file, destination_file), err_msg)

# ================================
# Basedir command
# ================================
def basedir():
    bindir = os.path.dirname(os.path.realpath(sys.argv[0]))     # Should be initial folder where install is started 
    if not bindir:
        bindir = "."
    else:
        return bindir

def localinstaller():
    if exists( basedir() + "/files"):
        return True
    else:
        return False 

# ================================
# check if is virtual environment 
# ================================
def is_vagrant():
    return os.path.isfile("/etc/is_vagrant_vm")

# ================================
# Popup window for Yes or No
# ================================
def yes_or_no(question, row = 1, col = 2):
    statwin.addstr(row, col, question+' (y/n): ', col_info)
    statwin.refresh()
    curses.curs_set(True)
    try:
        while True:
            char = statwin.getch()
            if char > 0:
                if chr(char) == 'y':
                    curses.curs_set(False)
                    return True
                if chr(char) == 'n':
                    curses.curs_set(False)
                    return False
    except KeyboardInterrupt:
        die("interrupted...")
        
# ================================
# Home directory
# ================================
def homedir():
    home = os.path.expanduser("~")
    return home

# ================================
# Check for PI version
# ================================
def getpiversion():
    myrevision = "0000"
    try:
        f = open('/proc/cpuinfo','r')
        for line in f:
            if line[0:8]=='Revision':
                length=len(line)
                myrevision = line[11:length-1]
        f.close()
    except:
         myrevision = "0000"
    # ==========================
    # Check for known models
    # ==========================    
    if   myrevision == "0002":                    model = "Model B Rev 1"
    elif myrevision == "0003":                    model = "Model B Rev 1"
    elif myrevision == "0004":                    model = "Model B Rev 2"
    elif myrevision == "0005":                    model = "Model B Rev 2"
    elif myrevision == "0006":                    model = "Model B Rev 2"
    elif myrevision == "0007":                    model = "Model A"
    elif myrevision == "0008":                    model = "Model A"
    elif myrevision == "0009":                    model = "Model A"
    elif myrevision == "000d":                    model = "Model B Rev 2A"
    elif myrevision == "000e":                    model = "Model B Rev 2A"
    elif myrevision == "000f":                    model = "Model B Rev 2A"
    elif myrevision == "0010":                    model = "Model B+"
    elif myrevision == "0013":                    model = "Model B+"
    elif myrevision == "900032":                  model = "Model B+"
    elif myrevision == "0011":                    model = "Compute Module"
    elif myrevision == "0014":                    model = "Compute Module"
    elif myrevision == "0012":                    model = "Model A+"
    elif myrevision == "0015":                    model = "Model A+"
    elif myrevision == "a01041":                  model = "Pi 2 Model B v1.1"
    elif myrevision == "a21041":                  model = "Pi 2 Model B v1.1"
    elif myrevision == "a22042":                  model = "Pi 2 Model B v1.2"
    elif myrevision == "900092":                  model = "Pi Zero v1.2"
    elif myrevision == "900093":                  model = "Pi Zero v1.3"
    elif myrevision == "9000C1":                  model = "Pi Zero W"
    elif myrevision == "a02082":                  model = "Pi 3 Model B"
    elif myrevision == "a22082":                  model = "Pi 3 Model B"
    elif myrevision == "a020d3":                  model = "Pi 3 Model B+"
    elif myrevision == "a03111":                  model = "Pi 4 1Gb"
    elif myrevision == "b03111":                  model = "Pi 4 2Gb"
    elif myrevision == "b03112":                  model = "Pi 4 2Gb"
    elif myrevision == "c03111":                  model = "Pi 4 4Gb"
    elif myrevision == "c03112":                  model = "Pi 4 4Gb"
    else:                                         model = "Unknown (" + myrevision + ")"
    return model

# ================================
# Check if we have a WiFi device
# ================================
def wifi_present():
    if is_vagrant():
        return False
    return exists("/sys/class/net/wlan0")   # Existance of WiFi interface indicates physical machine
  
############################################
# PHASE 0 install
############################################
def PHASE0():
    # ================================
    # Ask to continue
    # ================================
    if not yes_or_no("Do you want to install the ElimuPi build",1):
        abort('Installation aborted')
        
    # ================================
    # Check if on Linux and debian (requirement for ElimuPi)
    # ================================
    if platform.system() != 'Linux': 
        die('Incorrect OS [' + platform.system() + ']')
    
    # ================================
    # Display steps to complete for phase 0
    # ================================
    display_status()
    statwin.addstr( 1,2, "[ ] Update repositories", col_info)
    statwin.addstr( 2,2, "[ ] Update system", col_info)
    statwin.addstr( 3,2, "[ ] Install GIT", col_info)
    statwin.addstr( 4,2, "[ ] Clone ElimuPi repository", col_info)
    statwin.addstr( 5,2, "[ ] Write install status file", col_info)
    statwin.addstr( 6,2, "[ ] Setup fstab mount", col_info)
    statwin.addstr( 7,2, "[ ] Set pi password", col_info)
    statwin.addstr( 8,2, "[ ] Reboot", col_info)
    
    # ================================
    # Get latest updates 
    # ================================
    statwin.addstr( 1,3, "?" , col_info)
    statwin.refresh()
    result = sudo("apt-get update -y", "Unable to update RASPBERRYOS.") 
    display_log(result.output)
    statwin.addstr( 1,3, "*" , col_info_ok)
    statwin.refresh()
    
    # ================================
    # Update 
    # ================================
    statwin.addstr( 2,3, "?" , col_info)
    statwin.refresh()
    sudo("apt-get dist-upgrade -y", "Unable to upgrade RASPBERRYOS distribution.")
    display_log(result.output)
    statwin.addstr( 2,3, "*" , col_info_ok)
    statwin.refresh()
    
    # ================================
    # Get latest GIT
    # ================================
    statwin.addstr( 3,3, "?" , col_info)
    statwin.refresh()
    result = sudo("apt-get install -y git", "Unable to install Git.")
    display_log(result.output)
    statwin.addstr( 3,3, "*" , col_info_ok)
    statwin.refresh()

    # ================================
    # Clone the GIT repo or use local files.
    # ================================
    statwin.addstr( 4,3, "?" , col_info)
    statwin.refresh()
    if localinstaller():
        display_log("Using local files")
    else:
        result = sudo("rm -fr " + basedir() + "/build_elimupi", "Unable to update.")
          
        result = cmd("git clone --depth 1 " + base_git + " " + basedir() + "/build_elimupi") 
        result.result or die("Unable to clone Elimu installer repository.")
    statwin.addstr( 4,3, "*" , col_info_ok)
    statwin.refresh()
    
    # ================================
    # Make installer autorun
    # ================================
    #statwin.addstr( 5,3, "?" , col_info)
    #if not 'ElimuPi_installer.py' in open(homedir() + '/.bashrc').read():   #validate if it needs to be added
    #    file = open(homedir() + '/.bashrc', 'a')
    #    file.write( basedir() + '/ElimuPi_installer.py')       # Enable autostart on logon
    #    file.close()
    
    # ================================
    # Write install status to file
    # ================================
    file = open(base_build + '_install', 'w')
    file.write('1')                                                     # Write phase to file
    file.close()
    statwin.addstr( 5,3, "*" , col_info_ok)
    statwin.refresh()
    
    # ================================    
    #Setup and configure USB Automount
    # ================================
    statwin.addstr( 6,3, "?" , col_info)
    statwin.refresh()
    add_content_disk_to_fstab()
    statwin.addstr( 6,3, "*" , col_info_ok)
    statwin.refresh()

    # ================================
    # Set password
    # ================================
    if not is_vagrant():
        statwin.addstr( 7,2, "[ ] Set pi password to [" + base_passwd + "]", col_info)
        statwin.addstr( 7,3, "?" , col_info)
        statwin.refresh()
        result = sudo("echo \"" + base_user + ":" + base_passwd +"\"| sudo chpasswd ") 
        result.result or die("Unable to set the password")
        statwin.addstr( 7,3, "*" , col_info_ok)
        statwin.refresh()
    
    # ================================
    # Reboot
    # ================================
    statwin.addstr( 8,3, "?" , col_info)
    statwin.refresh()
    if not yes_or_no("Press 'y' to reboot the ElimuPi",9):
        abort('Installation aborted')
    result = sudo("reboot") 
    result.result or die("Unable to reboot Raspbian.")

############################################
# PHASE 1 install
############################################
def PHASE1():
    # ================================
    # Ask to continue
    # ================================
    if not yes_or_no("Do you want to continue the install the ElimuPi build"):
        die('Installation aborted')

    statwin.addstr(1,2,"[ ] Update to latest OS")
    statwin.addstr(2,2,"[ ] Update Raspberry PI firmware")
    statwin.addstr(3,2,"[ ] Setup webserver")
    statwin.addstr(4,2,"[ ] Setup Network")
    statwin.addstr(5,2,"[ ] Install FDroid")
    statwin.addstr(6,2,"[ ] Install Kolibri")
    statwin.addstr(7,2,"[ ] Install Citadel")
    statwin.addstr(8,2,"[ ] Install Kiwix")
    statwin.addstr(9,2,"[ ] Install Moodle")
    statwin.refresh()
            
    # ================================
    # Get latest package info 
    # ================================
    statwin.addstr( 1,3, "?" , col_info)
    statwin.refresh()
    result = sudo("apt-get update -y") 
    result.result or die("Unable to update.")
    statwin.addstr( 1,3, "*" , col_info_ok)
    statwin.refresh()
    
    # ================================
    # Update Raspi firmware
    # ================================
    if not is_vagrant():
        statwin.addstr( 2,3, "?" , col_info)
        statwin.refresh()
        display_log("Update RaspberryPi firmware...")
        result = sudo("yes | sudo rpi-update") 
        result.result or die("Unable to upgrade Raspberry Pi firmware")
        display_log("Update RaspberryPi firmware completed...", col_log_ok)
        statwin.addstr( 2,3, "*" , col_info_ok)
        statwin.refresh()
    
    # ================================
    # Install webserver
    # ================================
    statwin.addstr( 3,3, "?" , col_info)
    statwin.refresh()
    install_web_interface()
    statwin.addstr( 3,3, "*" , col_info_ok)
    statwin.refresh()
    
    # ================================
    # Setup Network
    # ================================
    statwin.addstr( 4,3, "?" , col_info)
    statwin.refresh()
    install_network()
    statwin.addstr( 4,3, "*" , col_info_ok)
    statwin.refresh()
    
    # ================================
    # FDROID
    # ================================
    statwin.addstr( 5, 3, "?" , col_info)
    statwin.refresh()
    install_fdroid()
    statwin.addstr( 5, 3, "*" , col_info_ok)
    statwin.refresh()
    
    # ================================
    # Install Kolibri
    # ================================
    statwin.addstr( 6, 3, "?" , col_info)
    statwin.refresh()
    install_kolibri()
    statwin.addstr( 6, 3, "*" , col_info_ok)
    statwin.refresh()
    
    # ================================
    # Install optional Citadel
    # ================================
    if args.install_citadel:
        statwin.addstr( 7, 3, "?" , col_info)
        statwin.refresh()
        install_citadel()
        statwin.addstr( 7, 3, "*" , col_info_ok)
        statwin.refresh()
    else:
        statwin.addstr( 7, 3, "-" , col_info)
        statwin.refresh()
    
    # ================================
    # install the kiwix server (but not content)
    # ================================
    statwin.addstr( 8, 3, "?" , col_info)
    statwin.refresh()
    install_kiwix()
    statwin.addstr( 8, 3, "*" , col_info)
    statwin.refresh()

    # ================================
    # Install Moodle
    # ================================
    if args.install_moodle:
        statwin.addstr( 9, 3, "?" , col_info)
        statwin.refresh()
        install_moodle()
        statwin.addstr( 9, 3, "*" , col_info)
        statwin.refresh()
    else:
        statwin.addstr( 9, 3, "-" , col_info)
    
    # ================================
    # record the version of the installer we're using - this must be manually
    # updated when you tag a new installer
    # ================================
    sudo("sh -c 'echo " + base_build + " > /etc/elimupi-installer-version'", "Unable to record ELIMUPI version.")
    
    # ================================
    # Final messages
    # ================================
    display_log("ELIMUPI image has been successfully created.", col_log_ok)
    display_log("It can be accessed at: http://" + base_ip + "/", col_log_ok)

    # ================================
    # Reboot
    # ================================
    if yes_or_no("Reboot for normal operation", 9):
        sudo("reboot", "Unable to reboot Raspbian.")
    else:
        abort("Installation successful, please reboot")

# ================================
# infowin display
# ================================
def display_info():
    infowin.bkgd(' ', col_info)
    infowin.addstr(0,2,"[ Info ]")
    infowin.addstr(0,12,"[ Phase " + install_phase + " ]")          # Phase of the installation
    infowin.addstr(1,2,"ElimuPi build : " + base_build )            # Build of the installer
    infowin.addstr(2,2,'Hardware      : ' + getpiversion() )        # Model of the PI
    infowin.addstr(3,2,'Platform      : ' + platform.platform() )   # Platform : Linux-4.9.41-v7+-armv7l-with-debian-9.1
    infowin.addstr(4,2,'System        : ' + platform.system() )     # System   : Linux
    infowin.addstr(5,2,'OS Release    : ' + platform.release() )    # Release  : 4.9.41-v7+
    infowin.addstr(6,2,'OS Version    : ' + platform.version() )    # Version  : #1023 SMP Tue Aug 8 16:00:15 BST 2017
    infowin.refresh()

# ================================
# statwin display
# ================================
def display_status():
    statwin.clear()
    statwin.bkgd(' ', col_info)
    statwin.border(0)
    statwin.addstr( 0,2, "[ ElimuPi Installation ]", col_info)
    statwin.refresh()

# ================================
# log display
# ================================
def display_log(message, attribute=col_log):
    message_string = str(message)
    current_y_pos = logwin.getyx()[0]
    logwin.addstr(current_y_pos + 1, 0, "> " + message_string, attribute)
    current_y_pos = logwin.getyx()[0]
    logwin.refresh(current_y_pos - (logwin_dheight - logwin_posy), 0, logwin_posy  , logwin_posx , logwin_dheight, logwin_dwidth )

# ================================
# Add locales for en_GB and sw_KE to environment
# ================================
def install_locales():
    # modify /etc/locale.gen
    # run  locale-gen
    #$ sudo locale-gen es_AR
    #$ sudo locale-gen es_AR.UTF-8
    #$ sudo update-locale
    #$ sudo service apache2 restart
    return  True

# ================================
# Setup network configuration
# ================================
def install_network():
    # ================================
    # Setup WiFi if present
    # ================================
    if wifi_present() and args.install_wifi:
        install_wifi()
    # ================================
    # install dnsmasq
    # ================================
    install_dnsmasq()
    # ================================
    # Update hostname (LAST!)
    # ================================
    if not is_vagrant():
        cp("files/hosts", "/etc/hosts", "Unable to copy hosts file.")
        cp("files/hostname", "/etc/hostname", "Unable to copy hostname file.")
        result = sudo("chmod 644 ElimuPi_installer.py") 
        result.result or die("Unable to change file permissions.")
    return True
 
############################################
#    Main code start
############################################
# ================================
# Construct install data
# ================================
install_data = {
    'commands': [
        {'step': 1,'command': 'update_apt', 'title': 'APT update'},
        {'step': 2,'command': 'update_os', 'title': 'OS update'},
        {'step': 3,'command': 'install_wifi', 'title': 'Install WiFi'},
        ]
    }

# ================================
# Check if installer has been run before
# ================================
if os.path.isfile(base_build + '_install'):
    logwin.addstr
    display_log("Continue install after reboot")
    # get phase
    install_phase = open(base_build + '_install').read()
else: 
    install_phase = "0"

curses.curs_set(False)
# ================================
# Display info
# ================================
display_info()

# ================================
# Display status window
# ================================
display_status()

# display_log("")

# ================================
# Display menu
# ================================
# processmenu(menu_data)

# ================================
# Start installer phase
# ================================
if   install_phase == "0":
    PHASE0()
elif install_phase == "1":
    PHASE1()
else: 
    die("Invalid installer state, installation aborted")

curses.endwin() #VITAL! This closes out the menu system and returns you to the bash prompt.
os.system('clear')
