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
      "mkdir /tmp/templates"
    ]
  }

  provisioner "file" {
    source = "ansible/templates"
    destination = "/tmp/templates"
  }

  provisioner "shell" {
    inline = [
      # "lsblk",
      # "df -h",
      # "ls -lah /",
      "apt-get update",
      "apt-get install ansible -y"
    ]
  }

  provisioner "ansible-local" {
    playbook_file = "ansible/playbook.yml"
    extra_arguments = [
      "--extra-vars template_path=/tmp/templates/",
      "--verbose"
    ]
  }

  # provisioner "shell" {
  #   inline = [
  #     "ls -lah /tmp",
  #     "ls -lah /tmp/templates",
  #     "ls -lah /tmp/templates/*"
  #   ]
  # }

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
