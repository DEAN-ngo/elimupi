source "arm-image" "elimupi-raspios-buster" {
  iso_url      = "https://downloads.raspberrypi.org/raspios_armhf/images/raspios_armhf-2021-05-28/2021-05-07-raspios-buster-armhf.zip"
  iso_checksum = "sha256:b6c04b34d231f522278fc822d913fed3828d0849e1e7d786db72f52c28036c62"
  target_image_size = 10737418240 # 10 GB
}

# variable "home" {
#   default = env("HOME")
# }

build {
  sources = [ "source.arm-image.elimupi-raspios-buster" ]

  provisioner "shell" {
    inline = [
      "echo 'before install software-properties-common'",
    ]
  }

  provisioner "shell" {
    inline = [
      "apt-get install software-properties-common -y",
    ]
  }

  provisioner "shell" {
    inline = [
      "echo 'before install add repo'",
    ]
  }

  provisioner "shell" {
    inline = [
      "apt-add-repository --yes --update deb http://ppa.launchpad.net/ansible/ansible/ubuntu bionic main",
    ]
  }

  provisioner "shell" {
    inline = [
      "echo 'before apt-key'",
    ]
  }

  // install the latest version of ansible from ubuntu.com
  provisioner "shell" {
    inline = [
      "apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 6125E2A8C77F2818FB7BD15B93C4A3FD7BB9C367",
    ]
  }

  provisioner "shell" {
    inline = [
      "echo 'before apt-get update'",
    ]
  }

  // install the latest version of ansible from ubuntu.com
  provisioner "shell" {
    inline = [
      "apt-get update",
      "apt-get install ansible-core -y"
    ]
  }

  provisioner "shell" {
    inline = [
      "echo 'before apt-get install ansible-core'",
    ]
  }

  provisioner "shell" {
    inline = [
      "apt-get install ansible-core -y"
    ]
  }

//  provisioner "shell" {
//    inline = [
//      # "lsblk",
//      # "df -h",
//      # "ls -lah /",
//      "apt-get install software-properties-common -y",
//      "apt-add-repository --yes --update ppa:ansible/ansible",
//      "sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 6125E2A8C77F2818FB7BD15B93C4A3FD7BB9C367",
//      "apt-get update",
//      "apt-get install ansible-core -y"
//    ]
//  }  
  
  provisioner "ansible-local" {
    playbook_file = "ansible/playbook-dockerpi.yml"
    extra_arguments = [
      "--verbose"
    ]
    playbook_dir = "ansible" 

  }

  post-processor "shell-local" {
    inline = ["wget https://raw.githubusercontent.com/Drewsif/PiShrink/master/pishrink.sh -O pishrink.sh"]
  }

  post-processor "shell-local" {
    inline = ["chmod +x pishrink.sh"]
  }

  post-processor "shell-local" {
    inline = ["./pishrink.sh output-elimupi-raspios-buster/image output.img"]
  }

  # post-processor "checksum" {
  #   checksum_types = ["sha1", "sha256"]
  #   output = "output/{{.BuildName}}_{{.ChecksumType}}.checksum"
  # }

  post-processor "shell-local" {
    inline = ["zip output output.img"]
  }
}
