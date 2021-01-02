#!/usr/bin/env python
#=========================================================================================================
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
#=========================================================================================================

import sys
import os
import subprocess
import argparse
import shutil
import urllib
import argparse
import platform
import curses           #curses is the interface for capturing key presses on the menu, os launches the files
import xml.etree.ElementTree as ET

from time import sleep
from builtins import true

#================================
# Settings for build
#================================
base_hostname       = "elimupi"                                     # Defaul hostname
base_user           = "pi"                                          # Default user name to use
base_passwd         = "elimupi"                                     # Default password for all services
base_ip_range       = "10.11.0"                                     # IP range (/24) for the WiFI interface
base_ip             = "10.11.0.1"                                   # Default IP address for the WiFi interface
base_subnet         = "255.255.255.0"                               # Base subnet
base_build          = "ELIMUPI-20180518-1"                          # Date of build
base_git            = "https://github.com/elimupi/elimupi2.0.git"   # Git location
base_wifi           = "wlan0"
installed_modules   = [];                                           # Installed modules

#================================
# Command line arguments
#================================
argparser = argparse.ArgumentParser()
argparser.add_argument( "--khan-academy",
                        choices=["none", "ka-lite"],
                        default="ka-lite",
                        help="Select Khan Academy package to install (default = \"ka-lite\")")
argparser.add_argument("--no-wifi",
                        dest="install_wifi",
                        action="store_false",
                        help="Do not configure local wifi hotspot.")
argparser.add_argument("--citadel",
                        dest="install_citadel",
                        action="store_false",
                        help="Install Citadel mail, chat and colaboration suite")
args = argparser.parse_args()

#================================
# Install USB mounter 
#================================
def install_usbmount():
    print('=========================================')
    print('Install and configure automount')
    print('=========================================')
    sudo("apt-get install usbmount ntfs-3g -y") or die("Unable to download usbmount")
    sudo("sed -i '/MountFlags=slave/c\MountFlags=shared' /lib/systemd/system/systemd-udevd.service") or die ("Unable to update udevd configuration (systemd-udevd.service)")
    #sudo("rm * /etc/usbmount/mount.d; rm * /etc/usbmount/umount.d")
    #### Automount location /var/run/usbmount/<label>
    sudo("chmod +x ./build_elimupi/files/01_create_label_symlink ./build_elimupi/files/01_remove_model_symlink")
    cp("./files/usbmount/usbmount.conf", "/etc/usbmount/")
    cp("./files/usbmount/01_create_label_symlink", "/etc/usbmount/mount.d/")
    cp("./files/usbmount/01_remove_model_symlink", "/etc/usbmount/umount.d")
    cp("./files/usbmount/usbmount@.service", "/etc/systemd/system/")
    cp("./files/usbmount/usbmount.rules", "/etc/udev/rules.d")

#================================
# Setup WiFi
#================================
def install_wifi():
    print( "=========================================")
    print( "Configuring Wifi components")
    print( "=========================================")

    sudo("ifconfig {} {}".format(base_wifi, base_ip)) or die("Unable to set {} IP address {}".format(base_wifi, base_ip))

    #Install hostapd, udhcpd
    sudo("apt-get -y install hostapd udhcpd") or die("Unable install hostapd and udhcpd.")
    
    #copy config files udhcpd
    cp("./files/udhcpd.conf", "/etc/udhcpd.conf") or die("Unable to copy uDHCPd configuration (udhcpd.conf)")
    cp("./files/udhcpd", "/etc/default/udhcpd") or die("Unable to copy UDHCPd configuration (udhcpd)")
    
    #copy config files hostapd
    cp("./files/hostapd", "/etc/default/hostapd") or die("Unable to copy hostapd configuration (hostapd)")
    cp("./files/hostapd.conf", "/etc/hostapd/hostapd.conf") or die("Unable to copy hostapd configuration (hostapd.conf)")
    
    #change udhcpd file
    sudo("sed -i '/interface	wlan0/c\interface	{}' /etc/udhcpd.conf".format(base_wifi)) 
    sudo("sed -i '/start/c\start        " + base_ip_range + ".11    #default: 192.168.0.20\' /etc/udhcpd.conf") or die("Unable to update uDHCPd configuration (udhcpd.conf)") 
    sudo("sed -i '/end/c\end        " + base_ip_range + ".199    #default: 192.168.0.254\' /etc/udhcpd.conf") or die("Unable to update uDHCPd configuration (udhcpd.conf)")
    sudo("sed -i '/^option.*subnet/c\option    subnet    " + base_subnet + "' /etc/udhcpd.conf") or die("Unable to update uDHCPd configuration (udhcpd.conf)")
    sudo("sed -i '/^opt.*router/c\opt    router    " + base_ip + "' /etc/udhcpd.conf") or die("Unable to update uDHCPd configuration (udhcpd.conf)")

    #change hostapd file
    sudo("sed -i '/interface=wlan0/c\interface={}' /etc/hostapd/hostapd.conf".format(base_wifi)) 

    #change iptables
    #sudo("sh -c 'echo 1 > /proc/sys/net/ipv4/ip_forward'") or die("Unable to set ipv4 forwarding")
    #cp("./files/sysctl.conf", "/etc/sysctl.conf") or die("Unable to copy sysctl configuration (sysctl.conf)")
    #sudo("iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE") or die("Unable to set iptables MASQUERADE on eth0.")
    #sudo("iptables -A FORWARD -i eth0 -o {} -m state --state RELATED,ESTABLISHED -j ACCEPT".format(base_wifi)) or die("Unable to forward wlan0 to eth0.")
    #sudo("iptables -A FORWARD -i {} -o eth0 -j ACCEPT".format(base_wifi)) or die("Unable to forward wlan0 to eth0.")
    #sudo("sh -c 'iptables-save > /etc/iptables.ipv4.nat'") or die("Unable to save iptables configuration.")
    sudo("ifconfig {}".format(base_wifi, base_ip)) or die("Unable to set wlan0 IP address (" + base_ip + ")")

    #start & enable hostapd, udhcpd
    sudo("service hostapd start") or die("Unable to start hostapd service.")
    sudo("service udhcpd start") or die("Unable to start udhcpd service.")
    sudo("update-rc.d hostapd enable") or die("Unable to enable hostapd on boot.")
    sudo("update-rc.d udhcpd enable") or die("Unable to enable UDHCPd on boot.")

    # udhcpd wasn't starting properly at boot (probably starting before interface was ready)
    # for now we we just force it to restart after setting the interface
    sudo("sh -c 'sed -i \"s/^exit 0//\" /etc/rc.local'") or die("Unable to remove exit from end of /etc/rc.local")
    sudo("sh -c 'echo rfkill unblock all >> /etc/rc.local; echo ifconfig {} {} >> /etc/rc.local; echo service udhcpd restart >> /etc/rc.local;'".format(base_wifi, base_ip)) or die("Unable to setup udhcpd reset at boot.")
    sudo("sh -c 'echo exit 0 >> /etc/rc.local'") or die("Unable to replace exit to end of /etc/rc.local")
    # sudo("ifdown eth0 && ifdown wlan0 && ifup eth0 && ifup wlan0") or die("Unable to restart network interfaces.")
    return True

#================================
# Install Moodle components
#================================
def install_moodle():
    print("=========================================")
    print("Installing Moodle components")
    print("=========================================")
    return True

#================================
# Install web interface
#================================
def install_web_interface():
    print( "=========================================")
    print( "Installing DEAN Web Interface components")
    print( "=========================================")
    # TODO: !!Put content on public GIT or use username and password!!!!!
    # sudo ('git clone https://github.com/DEANpeterV/ElimuPi-Web-Interface.git') 
    # ' https://github.com/DEANpeterV/ElimuPi-Web-Interface/archive/main.zip
    return True

#================================
# Install Khan Academy components 
#================================
def install_kalite():
    print("=========================================")
    print("Installing Khan Accedemy components")
    print("=========================================")
    sudo("apt-get install dirmngr -y") or die("Unable to install dirmmgr")
    sudo("sudo su -c 'echo deb http://ppa.launchpad.net/learningequality/ka-lite/ubuntu xenial main > /etc/apt/sources.list.d/ka-lite.list'")
    sudo("apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 74F88ADB3194DD81") or die("Unable to add key")
    sudo("apt-get update") or die("Unable to update the repository")
    sudo("apt-get install ka-lite-raspberry-pi -y") or die("Unable to install Ka-lite-raspberry-pi")
    #sudo("kalite manage setup --username=" + base_passwd + " --password=" + base_passwd + " --hostname=" + base_hostname + " --description=" + base_hostname) ### PBo 20180315 Removed unwanted confirmation
    cp("./files/kalite/settings.py", "/home/pi/.kalite/")
    sudo("systemctl start ka-lite") or die("Unable to start ka-lite")
    sudo("systemctl enable ka-lite")
    #sudo("sh -c '/usr/local/bin/kalite --version > /etc/kalite-version'") or die("Unable to record kalite version")
    return True

def install_ka_languague():
    print("=========================================")
    print("Setup Language for Khan Academy")
    print("=========================================")
    sudo("su pi -c '/usr/bin/kalite manage retrievecontentpack local en /var/run/usbmount/Content/khan/en.zip'")

def install_kiwix():
    print("=========================================")
    print("Installing KIWIX components")
    print("=========================================")
    # Get release rss data from mirror
    file = urllib2.urlopen('https://ftp.nluug.nl/pub/kiwix/release/kiwix-tools/feed.xml')
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
    print("latest_release:" + latest_release_name )
    
    sudo("mkdir -p /var/kiwix/bin") or die("Unable to make create kiwix directories")
    
    # get release for Linux-armhf from mirror
    sudo("curl -s " + latest_release + " | tar xz -C /home/pi/")
    
    # Copy files we need from the toolset
    cp("./" + latest_release_name + "/kiwix-manage", "/var/kiwix/bin/")
    cp("./" + latest_release_name + "/kiwix-read", "/var/kiwix/bin/")
    cp("./" + latest_release_name + "/kiwix-search", "/var/kiwix/bin/")
    cp("./" + latest_release_name + "/kiwix-serve", "/var/kiwix/bin/")
    
    # Copy config files
    cp("./files/kiwix/kiwix-start.pl", "/var/kiwix/bin/kiwix-start.pl") or die("Unable to copy dean-kiwix-start wrapper")
    sudo("chmod +x /var/kiwix/bin/kiwix-start.pl") or die("Unable to set permissions on dean-kiwix-start wrapper")
    cp("./files/kiwix/kiwix-service", "/etc/init.d/kiwix") or die("Unable to install kiwix service")
    sudo("chmod +x /etc/init.d/kiwix") or die("Unable to set permissions on kiwix service.")
    sudo("update-rc.d kiwix defaults") or die("Unable to register the kiwix service.")
    sudo("systemctl daemon-reload") or die("systemctl daemon reload failed")
    sudo("systemctl start kiwix") or die("Unable to start the kiwix service")
    sudo("systemctl enable kiwix") or die("Unable to enable the kiwix service")
    # PBo 20180312-07 sudo("service kiwix start") or die("Unable to start the kiwix service.")
    #sudo("sh -c 'echo {} >/etc/kiwix-version'".format(kiwix_version)) or die("Unable to record kiwix version.")
    return True

#===dnsmasq===#
def install_dnsmasq():
    print("=========================================")
    print("Installing DNS components")
    print("=========================================")  
    sudo("apt-get install dnsmasq -y") or die("Unable to install dnsmasq.")
    cp("./files/hosts", "/etc/hosts") or die("Unable to copy file hosts (/etc/hosts)")
    #sudo("rm /etc/dnsmasq.conf")
    
    print("=========================================")
    print("Setup NGINX domains")
    print("=========================================") 
    cp("./files/nginx/admin.local", "/etc/nginx/sites-available/") or die("Unable to copy file admin.local (nginx)")
    cp("./files/nginx/fdroid.local", "/etc/nginx/sites-available/") or die("Unable to copy file fdroid.local (nginx)")
    cp("./files/nginx/files.local", "/etc/nginx/sites-available/") or die("Unable to copy file files.local (nginx)")
    cp("./files/nginx/kahn.local", "/etc/nginx/sites-available/") or die("Unable to copy file kahn.local (nginx)")
    cp("./files/nginx/wiki.local", "/etc/nginx/sites-available/") or die("Unable to copy file wiki.local (nginx)")
    sudo("systemctl restart nginx") or die("Unable to restart nginx")

#================================
# Setup Citadel mail suite
# https://dzone.com/articles/how-to-install-citadel-mail-server-on-ubuntu-1604-1
# Note ths installer gives a GUI to configure
#================================
def install_citadel():
    print("=========================================")
    print("Installing Citadel components")
    print("=========================================")
    sudo("apt-get install citadel-suite -y") or die("Unable to install Citadel")
    sudo("/usr/lib/citadel-server/setup") or die("Unable to setup Citadel")
    return true

#===SUDO===#
def sudo(s):
   return cmd("sudo DEBIAN_FRONTEND=noninteractive %s" % s)

#================================
# die command
#================================
def die(d):
    print("Error: " + str(d))
    sys.exit(1)

#================================
# CMD command
#================================
def cmd(c):
    result = subprocess.Popen(c, shell=True, stdin=subprocess.PIPE, stderr=subprocess.PIPE, close_fds=True)
    try:
        result.communicate()
    except KeyboardInterrupt:
        pass
    return (result.returncode == 0)

#================================
# exists command
#================================
def exists(p):
    return os.path.isfile(p) or os.path.isdir(p)

#================================
# Copy command
#================================    
def cp(s, d):
    if localinstaller():
        return sudo("cp %s/%s %s" % (basedir(), s, d))
    else:
        return sudo("cp %s/%s %s" % (basedir() + "/build_elimupi", s, d))

#================================
# Basedir command
#================================
def basedir():
    bindir = os.path.dirname(os.path.realpath(sys.argv[0]))     # Should be initial folder where install is started 
    if not bindir:
        bindir = "."
    else:
        return bindir

def localinstaller():
    if exists( basedir() + "./files"):
        return True
    else:
        return False 

#================================
# check if is virtual environment 
#================================
def is_vagrant():
    return os.path.isfile("/etc/is_vagrant_vm")

def yes_or_no(question):
    while "the answer is invalid":
        reply = str(raw_input(question+' (y/n): ')).lower().strip()
        if reply[0] == 'y':
            return True
        if reply[0] == 'n':
            return False

#================================
# Home directory
#================================
def homedir():
    home = os.path.expanduser("~")
    return home

#================================
# Check for PI version
#================================
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

#================================
# Check if we have a WiFi device
#================================
def wifi_present():
    if is_vagrant():
        return False
    return exists("/sys/class/net/wlan0")   # Existance of WiFi interface indicates physical machine

############################################
# PHASE 0 install
############################################
def PHASE0():
    #================================
    # Ask to continue
    #================================
    if not yes_or_no("Do you want to install the ElimuPi build"):
        abort('Installation aborted')
    #================================
    # Check if on Linux and debian (requirement for ElimuPi)
    #================================
    if platform.system() != 'Linux': 
        die('Incorrect OS [' + platform.system() + ']')
    
    if platform.linux_distribution().index('debian'):
        die('Incorrect distribution [' + platform.linux_distribution() + ']')
    
    #================================
    # Get latest updates 
    #================================
    sudo("apt-get update -y") or die("Unable to update.")
    sudo("apt-get dist-upgrade -y") or die("Unable to upgrade Raspbian.")
    
    #================================
    # Get latest GIT
    #================================
    sudo("apt-get install -y git") or die("Unable to install Git.")
    
    #================================
    # Vargrant build detection (?) 
    #================================
    if is_vagrant():
        sudo("mv /vagrant/sources.list /etc/apt/sources.list")
        
    #================================
    # Clone the GIT repo or use local files.
    #================================
    if localinstaller():
        print("Using local files ")
    else:
        print("Fetching files from GIT to " + basedir() ) 
        sudo("rm -fr " + basedir() + "/build_elimupi")  
        cmd("git clone --depth 1 " + base_git + " " + basedir() + "/build_elimupi") or die("Unable to clone Elimu installer repository.")


    #================================
    # Make installer autorun
    #================================
    if not 'ElimuPi_installer.py' in open(homedir() + '/.bashrc').read():   #validate if it needs to be added
        file = open(homedir() + '/.bashrc', 'a')
        file.write( basedir() + '/ElimuPi_installer.py')       # Enable autostart on logon
        file.close()
        print("Autostart enabled")
    else:
        print("Autostart already enabled")
      
    #================================
    # Write install status to file
    #================================
    file = open(base_build + '_install', 'w')
    file.write('1')                                                     # Write phase to file
    file.close()
  
    #================================    
    #Setup and configure USB Automount
    #================================
    install_usbmount()
  
    #================================
    # Set password
    #================================
    if not is_vagrant():
        print("Set user password for "+ base_user + " to " + base_passwd )
        sudo("echo \"" + base_user + ":" + base_passwd +"\"| sudo chpasswd ") or die("Unable to set the password")
    
    #================================
    # Reboot
    #================================
    print("---------------------------------------------------------")    
    print("Rebooting sytem required to enable all updates")
    print("Press enter to reboot")
    print("---------------------------------------------------------")
    raw_input('')
    sudo("reboot") or die("Unable to reboot Raspbian.")

############################################
# PHASE 1 install
############################################
def PHASE1():
    #================================
    # Ask to continue
    #================================
    if not yes_or_no("Do you want to continue the install the ElimuPi build"):
        abort('Installation aborted')
        
    #================================
    # Get latest package info 
    #================================
    sudo("apt-get update -y") or die("Unable to update.")
    
    #================================
    # Update Raspi firmware
    #================================
    if not is_vagrant():
        sudo("yes | sudo rpi-update") or die("Unable to upgrade Raspberry Pi firmware")
        
    #================================
    # Setup wifi hotspot
    #================================
    if wifi_present() and args.install_wifi:
        install_wifi() or die("Unable to install WiFi.")
               
    #================================
    # KAHN academy (optional)
    #================================
    if args.khan_academy == "ka-lite":
        install_kalite() or die("Unable to install KA-Lite.")
    
    #================================
    # Install Citadel
    #================================
    if args.install_citadel:
        install_citadel() or die("Unable to install Citadel.")
        
    #================================
    # install the kiwix server (but not content)
    #================================
    install_kiwix()
    
    #================================
    # install dnsmasq
    #================================
    install_dnsmasq()
    
    #================================
    # install the language for Khan
    #================================
    install_ka_languague()

    #================================
    # Update hostname (LAST!)
    #================================
    if not is_vagrant():
        cp("files/hosts", "/etc/hosts") or die("Unable to copy hosts file.")
        cp("files/hostname", "/etc/hostname") or die("Unable to copy hostname file.")
        sudo("chmod 644 ElimuPi_installer.py") or die("Unable to change file permissions.")
    
    #================================
    # record the version of the installer we're using - this must be manually
    # updated when you tag a new installer
    #================================
    sudo("sh -c 'echo " + base_build + " > /etc/elimupi-installer-version'") or die("Unable to record ELIMUPI version.")
    
    #================================
    # Final messages
    #================================
    print("ELIMUPI image has been successfully created.")
    print("It can be accessed at: http://" + base_ip + "/")

    #================================
    # Reboot
    #================================
    print("---------------------------------------------------------")    
    print("Rebooting sytem required to enable all updates")
    print("Press enter to reboot")
    print("---------------------------------------------------------")
    raw_input('')
    sudo("reboot") or die("Unable to reboot Raspbian.")

# ================================
# This function displays the appropriate menu and returns the option selected
# ================================
def runmenu(menu, parent):
    # work out what text to display as the last menu option
    if parent is None:
        lastoption = "Exit"
    else:
        lastoption = "Return to %s menu" % parent['title']

    optioncount = len(menu['options']) # how many options in this menu
    
    pos=0 #pos is the zero-based index of the hightlighted menu option. Every time runmenu is called, position returns to 0, when runmenu ends the position is returned and tells the program what opt$
    oldpos=None # used to prevent the screen being redrawn every time
    x = None #control for while loop, let's you scroll through options until return key is pressed then returns pos to program
    
    # Loop until return key is pressed
    while x !=ord('\n'):
        if pos != oldpos:
            oldpos = pos
            screen.border(0)
            screen.addstr(2,2, menu['title'], curses.A_STANDOUT) # Title for this menu
            screen.addstr(4,2, menu['subtitle'], curses.A_BOLD) #Subtitle for this menu

        # Display all the menu items, showing the 'pos' item highlighted
        for index in range(optioncount):
            textstyle = n
            if pos==index:
                textstyle = h
            screen.addstr(5+index,4, "%d - %s" % (index+1, menu['options'][index]['title']), textstyle)
        # Now display Exit/Return at bottom of menu
        textstyle = n
        if pos==optioncount:
            textstyle = h
        screen.addstr(5+optioncount,4, "%d - %s" % (optioncount+1, lastoption), textstyle)
        screen.refresh()
        # finished updating screen

        x = screen.getch() # Gets user input

        # What is user input?
        if x >= ord('1') and x <= ord(str(optioncount+1)):
            pos = x - ord('0') - 1 # convert keypress back to a number, then subtract 1 to get index
        elif x == 258: # down arrow
            if pos < optioncount:
                pos += 1
            else: 
                pos = 0
        elif x == 259: # up arrow
            if pos > 0:
                pos += -1
            else: 
                pos = optioncount

        # return index of the selected item
        return pos

# ================================
# This function calls showmenu and then acts on the selected item
# ================================
def processmenu(menu, parent=None):
    optioncount = len(menu['options'])
    exitmenu = False
    while not exitmenu: #Loop until the user exits the menu
        getin = runmenu(menu, parent)
        if getin == optioncount:
            menu = True
        elif menu['options'][getin]['type'] == COMMAND:
            curses.def_prog_mode()    # save curent curses environment
            os.system('reset')
            if menu['options'][getin]['title'] == 'Pianobar':
                os.system('amixer cset numid=3 1') # Sets audio output on the pi to 3.5mm headphone jack
            screen.clear() #clears previous screen
            os.system(menu['options'][getin]['command']) # run the command
            screen.clear() #clears previous screen on key press and updates display based on pos
            curses.reset_prog_mode()   # reset to 'current' curses environment
            curses.curs_set(1)         # reset doesn't do this right
            curses.curs_set(0)
            os.system('amixer cset numid=3 2') # Sets audio output on the pi back to HDMI
        elif menu['options'][getin]['type'] == MENU:
            screen.clear() #clears previous screen on key press and updates display based on pos
            processmenu(menu['options'][getin], menu) # display the submenu
            screen.clear() #clears previous screen on key press and updates display based on pos
        elif menu['options'][getin]['type'] == EXITMENU:
            exitmenu = True

############################################
#    Main code start
############################################

# ================================
# Init screen display
# ================================
screen = curses.initscr() #initializes a new window for capturing key presses

curses.noecho() # Disables automatic echoing of key presses (prevents program from input each key twice)
curses.cbreak() # Disables line buffering (runs each key as it is pressed rather than waiting for the return key to pressed)
curses.start_color() # Lets you use colors when highlighting selected menu option
screen.keypad(1) # Capture input from keypad

#================================
# Change this to use different colors when highlighting
#================================
curses.init_pair(1, curses.COLOR_YELLOW, curses.COLOR_RED)  # Sets up color pair #1
curses.init_pair(2, curses.COLOR_BLUE, curses.COLOR_WHITE)  # 
curses.init_pair(3, curses.COLOR_YELLOW, curses.COLOR_BLUE) #
h = curses.color_pair(1) #h is the coloring for a highlighted menu option
n = curses.A_NORMAL #n is the coloring for a non highlighted menu option

# ================================
# Construct menu
# ================================
MENU = "menu"
COMMAND = "command"
EXITMENU = "exitmenu"

menu_data = {
    'title': "ElimuPi installer", 'type': MENU, 'subtitle': "Please select an option...",
    'options':[
        { 'title': "XBMC", 'type': COMMAND, 'command': 'xbmc' },
        { 'title': "Emulation Station - Hit F4 to return to menu, Esc to exit game", 'type': COMMAND, 'command': 'emulationstation' },
        { 'title': "Ur-Quan Masters", 'type': COMMAND, 'command': 'uqm' },
        { 'title': "Dosbox Games", 'type': MENU, 'subtitle': "Please select an option...",
            'options': [
                { 'title': "Midnight Rescue", 'type': COMMAND, 'command': 'dosbox /media/samba/Apps/dosbox/doswin/games/SSR/SSR.EXE -exit' },
                { 'title': "Outnumbered", 'type': COMMAND, 'command': 'dosbox /media/samba/Apps/dosbox/doswin/games/SSO/SSO.EXE -exit' },
                { 'title': "Treasure Mountain", 'type': COMMAND, 'command': 'dosbox /media/samba/Apps/dosbox/doswin/games/SST/SST.EXE -exit' },
                ]
            },
        { 'title': "Pianobar", 'type': COMMAND, 'command': 'clear && pianobar' },
        { 'title': "Windows 3.1", 'type': COMMAND, 'command': 'dosbox /media/samba/Apps/dosbox/doswin/WINDOWS/WIN.COM -conf /home/pi/scripts/dosbox2.conf -exit' },
        { 'title': "Reboot", 'type': MENU, 'subtitle': "Select Yes to Reboot",
            'options': [
                {'title': "NO", 'type': EXITMENU, },
                {'title': "", 'type': COMMAND, 'command': '' },
                {'title': "", 'type': COMMAND, 'command': '' },
                {'title': "", 'type': COMMAND, 'command': '' },
                {'title': "YES", 'type': COMMAND, 'command': 'sudo shutdown -r -time now' },
                {'title': "", 'type': COMMAND, 'command': '' },
                {'title': "", 'type': COMMAND, 'command': '' },
                {'title': "", 'type': COMMAND, 'command': '' },
                ]
            },
        ]
    }

#================================
# Check if installer has been run before
#================================
if os.path.isfile(base_build + '_install'):
    print("Continue install after reboot")
    # get phase
    install_phase = open(base_build + '_install').read()
else: 
    install_phase = "0"


#================================
# Display info
#================================
curses.init_pair(1,curses.COLOR_WHITE, curses.COLOR_BLUE) # Sets up color pair #1
curses.init_pair(2,curses.COLOR_BLUE, curses.COLOR_WHITE) # Sets up color pair #1
h = curses.color_pair(1) #h is the coloring for a highlighted menu add_standard_options
col_info = curser.color_pair(1)
col_stat = curser.color_pair(2)

statwin = curses.newwin( curses.LINES - 14, curses.COLS - 8 ,2,4)
statwin.bkgd(' ', col_stat)
statwin.border(0)
statwin.addstr(0,2,"[ Status ]")
statwin.addstr(2,2, "Build 1" )
statwin.addstr(3,2, "Line2")
statwin.refresh()

infowin = curses.newwin(9, curses.COLS - 8 , curses.LINES - 10 , 4) # 
infowin.bkgd(' ', col_info)
infowin.border(0)
infowin.addstr(0,2,"[ Info : phase " + install_phase + " ]")
infowin.addstr(1,2,"ElimuPi build : " + base_build )
infowin.addstr(2,2,'Hardware      : ' + getpiversion() )              # Model of the PI
infowin.addstr(3,2,'Platform      : ' + platform.platform() )          # Platform : Linux-4.9.41-v7+-armv7l-with-debian-9.1
infowin.addstr(4,2,'System        : ' + platform.system() )           # System   : Linux
infowin.addstr(5,2,'OS Release    : ' + platform.release() )          # Release  : 4.9.41-v7+
infowin.addstr(6,2,'OS Version    : ' + platform.version() )          # Version  : #1023 SMP Tue Aug 8 16:00:15 BST 2017
infowin.addstr(7,2,"Install phase : (" + install_phase + ")")         # Installer phase
infowin.refresh()

exit(0)
#================================
# Display menu
#================================
processmenu(menu_data)

# ================================
# Start installer phase
# ================================
if   install_phase == "0":
    PHASE0()
elif install_phase == "1":
    PHASE1()
else: 
    print("Invallid installer state")
    die('Installation aborted')

curses.endwin() #VITAL! This closes out the menu system and returns you to the bash prompt.
os.system('clear')