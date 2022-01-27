#!/bin/bash

# Change max upload size
ini=$(php -r 'echo php_ini_loaded_file();')
sudo sed -i 's/^upload_max_filesize.*/upload_max_filesize = 100MB/' ${ini}

replace="fpm"
fpm="${ini/cli/"$replace"}"
sudo sed -i 's/^upload_max_filesize.*/upload_max_filesize = 100MB/' ${fpm}
