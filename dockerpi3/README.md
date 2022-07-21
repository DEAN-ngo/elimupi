# How to create a new SD-card image for ElimuPi
## Step 1
This will create a docker image which contains a modified
sdcard image of Raspios. This modification enables the
ssh-daemon to be started, which is required for accessing
the virtual Pi with Ansible.  
```
docker build -f Dockerfile.image -t dean/pi3image:v1.0.0 .
```

## Step 2
This will create another docker image. This one contains an
emulated Raspberry Pi3.  
```
docker build -f Dockerfile.emulator -t dean/pi3emulator:v1.0.0 .
```

## Step 3
When the image-container is started, the sdcard-image will be
copied to the _sd_ docker-volume (which will be created on the fly).  
The name of the sd-card image in the sd volume is _sdcard.img_.  
Additionally 2 files (kernel and network firmware) will be copied
to the _sd_ volume. These files a required for starting the Pi3
emulator.  
```
docker run --rm -v sd:/sd dean/pi3image:v1.0.0
```

## Step 4
The _sd_ volume can be used by the emulator.  
Changes to the virtual sd-card (via the emulator) are saved to
the image file _sdcard.img_.  

Start the emulator with the following command:  
```
docker run -ti -p 5022:22 -v sd:/sd dean/pi3emulator:v1.0.0
```

When the emulators boot process is finished, which can take
several minutes (message _Reached target Multi-User System_ is displayed),
you can _ssh_ into the virtual Pi3:  
```
ssh pi@127.0.0.1 -p 5022
```

The password is the default pi-password, which can be found at [Google](https://letmegooglethat.com/?q=raspios+default+password).  

When you are finished changing the sdcard image, shutdown the emulator
with `sudo poweroff`. The will close the sdcard image.

When you made a mess and want to start all over again, remove the _sd_
volume. Probably you have to remove one or more emulator containers
holding the docker volume _sd_.  
When the volume is removed, go to _Step 3_.  

## Step 5
When you are satisfied, write the image to a real sd-card.  
To access the sd-card image, start a temporary container which opens
the _sd_ docker volume:  
```
docker run -ti --rm --name sdcontainer -v sd:/sd ubuntu:20.04 /bin/bash
```

In a second shell session you can copy the sdcard image to your current
directory:   
```
docker cp sdcontainer:/sd/sdcard.img .
```

When you are finished copying the image, close the shell of the first session.
The container will automatically be removed.  
The image is now available in your current directory. The filename is _sdcard.img_.
You can write the image to a real sdcard.  
