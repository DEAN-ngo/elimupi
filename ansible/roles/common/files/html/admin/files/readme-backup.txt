To restore the LDAP database:
$ ldapadd -c -x -D 'cn=Manager,dc=elimupi,dc=local' -w elimupi -f ./ldap.ldif
