# Using packer to build the raspberry pi image

Packer plugin used: [packer-builder-arm-image](https://github.com/solo-io/packer-builder-arm-image)

Dependencies:

- `packer` - Well..
- `kpartx` - Mapping the partitons to mountable devices
- `qemu-user-static` - Executing arm binaries
- `go` - Building the plugin with go

Ubuntu install (20.04.03 server):

```bash
curl -fsSL https://apt.releases.hashicorp.com/gpg | sudo apt-key add -
sudo apt-add-repository "deb [arch=amd64] https://apt.releases.hashicorp.com $(lsb_release -cs) main"
sudo apt update && sudo apt install -y packer kpartx qemu-user-static
wget https://golang.org/dl/go1.17.linux-amd64.tar.gz -O /tmp/go1.17.linux-amd64.tar.gz
sudo tar -C /usr/local -xzf /tmp/go1.17.linux-amd64.tar.gz; rm /tmp/go1.17.linux-amd64.tar.gz
echo 'export PATH=$PATH:/usr/local/go/bin' >> /etc/profile
export PATH=$PATH:/usr/local/go/bin
```

Building the plugin:

```bash
go install github.com/solo-io/packer-builder-arm-image@master
mv ~/go/bin/packer-builder-arm-image .
```

Building the image:

```bash
packer build packer/image.pkr.hcl
```

Profit.
