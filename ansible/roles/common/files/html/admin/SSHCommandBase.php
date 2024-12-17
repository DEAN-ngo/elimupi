<?php

use phpseclib\Net\SSH2;

class SSHCommandBase{

    public $ssh;
    public $user;
    
    function __construct( $username, $password ){
        $this->ssh = new SSH2( SSH_URL );

        if( $this->isValidCommand( $username) 
            && $this->isValidCommand( $password )){
                if ( ! $this->ssh->login($username, $password) ) {
                    $this->page_not_found();
                }
                else
                    $this->user = $username;
            }
        else
            $this->page_not_found();
    }

    function page_not_found(){
        http_response_code(304);
        exit();
    }
}

?>