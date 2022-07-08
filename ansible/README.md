Elimupi Ansible playbook
=============
Prerequisites:
 - ansible 2.10 > installed.
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

`ansible-vault edit group_vars/all/secrets.yml`  
