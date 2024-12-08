<?php

require_once( '../settings.php' );

if( $_SERVER['REQUEST_METHOD'] == 'POST'){
    $req = $_POST[ 'command' ];
    if( isset( $_POST[ 'user' ] ))
        $user = $_POST[ 'user' ];
    if(isset( $_POST[ 'password' ]))
        $password = $_POST[ 'password' ];
    
    if( !empty( $req ))
        switch( $req ){
            case preg_match( "/Packages/", $req) > 0:
                new PackagesCommand( $req , $_POST );
            break;
    
            default:
                new SSHCommand( $req, $user, $password, $_POST );
        }
}
else{
    if( ! empty( $_GET['download'])){
        $file = '/tmp/' . basename($_GET['download']);
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: ".filesize($file));
        header("Content-Disposition: attachment; filename=\"".basename($file)."\"");
        readfile($file);
    }
}

?>