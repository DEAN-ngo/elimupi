<?php

class LDAP{

    private static $con;

    private static function connect(){
        
        self::$con = ldap_connect( "ldap://localhost" );

        ldap_set_option( self::$con, LDAP_OPT_PROTOCOL_VERSION, 3);

        ldap_bind( self::$con, "cn=Manager,dc=elimupi,dc=local", "elimupi" );

    }

    private static function disconnect(){
        return ldap_close( self::$con );
    }

    private static function accountExists( $dn, $cn ){
        $list = ldap_list( self::$con, $dn,  "cn=$cn" );
        if($list !== false){
            $entries = ldap_get_entries( self::$con, $list );
            return $entries['count'] > 0;
        }
        return false;
    }

    private static function makeDN( $type, $name = "" ){
        if( ! empty($name))
            $dn = "cn=$name,";
        else
            $dn = "";

        if( $type == 'students' )
            $dn .= 'ou=students';
        else if( $type == 'teachers' )
            $dn .= 'ou=teachers';
        else if( $type == 'it' )
            $dn .= 'ou=it';

        return $dn . ',o=school,dc=elimupi,dc=local';

    }

    private static function initEntry( $name, $sirName = null ){
        $entry[ 'objectClass' ][0] = 'person';
        $entry[ 'objectClass' ][1] = 'inetorgperson';
        $entry[ 'cn' ] = $name;
        $entry[ 'sn' ] = ' ';
        if( $sirName )
            $entry[ 'sn' ] = $sirName;
        $entry[ 'mail' ] = ' ';
        return $entry;
    }

    private static function initAccount( $name, $dn, $entry ){
        $entry = array_merge( self::initEntry( $name ), $entry );
        ldap_add( self::$con, $dn, $entry );
    }

    public static function addAccount(  String $type, String $name, String $pw, String $sirName = null ) : bool{
        
        if( ! extension_loaded('ldap')) return true;

        self::connect();

        if( self::$con ){

            $entry = self::initEntry( $name, $sirName );

            $entry[ 'userPassword' ] = self::makeHash( $pw );

            $dn = self::makeDN( $type );

            if( ! self::accountExists( $dn, $name ))
                ldap_add( self::$con, $dn, $entry );
            else
                self::updateAccount( 
                    $dn, 
                    array( 'userpassword' => self::makeHash( $pw ))
                );
        
            return self::disconnect();
        }
        return false;
    }

    private static function makeHash( $password )
    {
      $salt = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',4)),0,4);
      $hash = "{SSHA}".base64_encode(sha1($password.$salt, true).$salt);
      return $hash;
    }

    public static function updatePassword( $type, $name, $pw ) : bool {

        if( ! extension_loaded('ldap')) return true;

        self::connect();

        if( ! self::accountExists( self::makeDN( $type ), $name ) ){
            self::initAccount( 
                $name,
                self::makeDN( $type ), 
                array( 'userpassword' => self::makeHash( $pw )) 
            );
        }
        else
            return self::updateAccount( 
               self::makeDN( $type ), 
                array( 'userpassword' => self::makeHash( $pw ))
            );

        return self::disconnect();
    }

    public static function updateAccount( $dn, $entry ) : bool {

        if( self::$con ){
            return ldap_mod_replace( 
                self::$con, 
                $dn,
                $entry 
            );
        }
        return false;
    }

    public static function deleteAccount( $type, $name ) : bool {
        
        if( ! extension_loaded('ldap')) return true;

        self::connect();

        if( self::$con ){
            
            $ok = ldap_delete( 
                self::$con, 
                self::makeDN( $type, $name )
            );

            if( $ok ){
                self::disconnect();
                return true;
            }
            else{
                self::disconnect();
                return false;
            }
        }
        return false;
    }
}
