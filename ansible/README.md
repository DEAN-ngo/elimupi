Elimupi Ansible playbook
=============
Prerequisites:
 - ansible 2.9 > installed.
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

## How to update something in secrets.yml
First you need to decrypt the file `group_vars/all/secrets.yml` with the command:

`ansible-vault decrypt group_vars/all/secrets.yml`  

After that you can edit the file normally. When done you need to encrypt it again with the command:

`ansible-vault encrypt group_vars/all/secrets.yml` and the provide the same password as you used to decrypt it.