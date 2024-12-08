<?php

abstract class Command{
    function __construct( $ssh, $user ){
        $this->ssh = $ssh;
        $this->user = $user;
    }
}