<?php

class Response extends ResponseHeader{
    
    function __construct( $output ){
    
        parent::__construct();
    
        echo json_encode( $output );
    }
}

?>