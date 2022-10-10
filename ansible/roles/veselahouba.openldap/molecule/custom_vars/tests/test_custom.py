import os

import testinfra.utils.ansible_runner

testinfra_hosts = testinfra.utils.ansible_runner.AnsibleRunner(
    os.environ['MOLECULE_INVENTORY_FILE']).get_hosts('all')


def test_ldap_is_listening(host):
    ldap = host.socket('tcp://636')
    assert ldap.is_listening


def test_ldap_service_is_running(host):
    with host.sudo():
        assert host.service('slapd.service').is_enabled
        assert host.service('slapd.service').is_running


def test_domain_over_socket(host):
    ls = host.run("ldapsearch -Y EXTERNAL -H ldapi:/// -b 'dc=example,dc=com'")
    assert ls.rc == 0
    assert '# numEntries: 1' in ls.stdout


def test_domain_over_tcp(host):
    ls = host.run(
      "LDAPTLS_REQCERT=never ldapsearch -D " +
      "'cn=Manager,dc=example,dc=com' -w passme -H ldaps://127.0.0.1:636 " +
      "-b 'dc=example,dc=com'"
    )
    assert ls.rc == 0
    assert '# numEntries: 1' in ls.stdout
