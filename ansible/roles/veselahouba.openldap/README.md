# openldap_server

This roles installs the OpenLDAP server on the target machine. It has the
option to enable/disable SSL by setting it in defaults or overriding it.

## Requirements

This role requires community.general collection, Ansible 2.8 or higher, and platform requirements are listed in the metadata file.

## Role Variables

Please consult default/main.yml

## Examples

```YAML
- name: Install openldap
  hosts: all
  vars:
    openldap_server_enable_tls: true
    openldap_server_generate_cert: true
  roles:
    - role: veselahouba.openldap
```

## Dependencies

community.general collection

## License

BSD

## Author Information

Jan Michalek, inspired by Benno Joy
