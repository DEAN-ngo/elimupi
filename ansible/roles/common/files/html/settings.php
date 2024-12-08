<?php

// Debug info
// ToDo: remove this
error_reporting(0);

ini_set( 'display_errors', 0);

// The autoload of composer
require __DIR__ . '/vendor/autoload.php';

// The application autoload
spl_autoload_register( function( $class_name ){
    if( is_file(__DIR__ . '/admin/classes/' . $class_name . '.php'))
        require_once( __DIR__ . "/admin/classes/$class_name.php" );
    else
        require_once( $class_name . '.php' );
} );

// Timeout for request w/ file_get_contents:
//ini_set('default_socket_timeout', 180);
// must be set in php.ini

// The url of the Pi to talk to
// ToDo: There should be the possibility of specifying a comma separated list
define( "SSH_URL", "localhost");

// Constants for sync packages
define( 'chunkSize', 1000 );

define( 'elimuPiUrl', 'https://elimupi.online/');

define( 'elimuGoUrl', 'http://content_mobile.local/' );

define( 'remoteFolder', 'content/' );

define( 'packagesXml', remoteFolder . 'packages.xml' );

define( 'volumeLabel', 'Content' );

define( 'rootFolder', 'Packages' );

//define( 'localPath', '/var/run/usbmount' );
define( 'localPath', '/mnt/content' );

define( 'localPath2', '/mnt/content-1' );

define( 'localRepo', '/' . volumeLabel . '/' . rootFolder . '/' );

define( 'localRepo2', '/' . volumeLabel . '/' . rootFolder . '/' );

// Prefix for folders not verified
define( 'notVerifiedCharacter', '_' );

define( 'scriptsLocation', '/var/www/scripts' );

// For translations the corresponding locale should exist
// After adding a new locale it is needed to restart the machine
$str = shell_exec("locale -a | grep .utf8");
$ar = explode('.utf8' . "\n", $str);

// The first locale is the default
$codes = array( );
forEach( $ar as $code){
    array_push( $codes, str_replace("_", "-", $code ));
}

$i18n = new \Delight\I18n\I18n( $codes );
$i18n->setDirectory(__DIR__ . '/translations');
$i18n->setModule('messages');

// For conversion of username candidates to their ASCII equivalent an existing locale
// in UTF-8 should be set here. 
setLocale( LC_CTYPE, 'C.UTF-8' );

// See : https://github.com/delight-im/PHP-I18N#decide-on-your-initial-set-of-supported-locales
// ElimuPi.local?lang=[locale] is probably the most handy to override the browser request headers 
// as there are no natural language strings in request.php
$i18n->setLocaleAutomatically();

if( isset( $_GET[ 'logout' ])){
    header( 'Location: ./' );
}

function _f($text, ...$replacements) { global $i18n; return $i18n->translateFormatted($text, ...$replacements); }

?>