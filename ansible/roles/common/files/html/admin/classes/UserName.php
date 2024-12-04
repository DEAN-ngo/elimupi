<?php

/*
    Class to optimize input for linux user name.
    Only diacritical characters are converted so names can remain invalid usernames.
*/

class UserName{

    private $input = '';
    private $userName = '';

    function __construct( $str ){
        $this->input = str_replace( ' ', '', strtolower( $str ));
        $this->userName = $this->toASCII( );
    }

    public function toASCII( ){
        // This changes 'renée' into 'renee'
        $enc = mb_detect_encoding( $this->input );
        if( $enc )
            return iconv( $enc, 'ASCII//TRANSLIT', $this->input );
        else
            return $this->input;
    }

    public function isConverted(){
        return $this->userName !== $this->input;
    }

    public function get(){
        return $this->userName;
    }
}
?>