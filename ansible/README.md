Elimupi Ansible playbook
=============
Prerequisites:
 - ansible 2.9 > installed.
 - ssh_askpass installed.
 - local pi.

 Run ansible-play book against local raspberry pi:

 - install Ansible collections:

` ansible-galaxy collection install -r collections.yml`

 - install Ansible roles

`ansible-galaxy install -r roles.yml`

- run playbook

`ansible-playbook -i ./inventory.yml playbook-raspberrypi.yml`
