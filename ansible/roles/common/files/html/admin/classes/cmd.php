<?php

trait cmd{
    private function cmd( String $cmd ){
        $str = $this->ssh->exec( $cmd );
        $exit = intval($this->ssh->getExitStatus());
        
        if( $exit > 0 )
            Log::writeLine( "user: " . $this->user . " $cmd", $exit );

        return array(
            "str" => $str,
            "exit" => $exit
        );
    }
    
    private function moosh( $cmd ){
        $pathMoodle = '/var/moodle';
        return $this->cmd( "sudo moosh -p $pathMoodle $cmd" );
    }

    private function getUserGroups(){
        $res = $this->cmd( 'groups' );
        $lines = explode("\n", $res['str']);
        array_pop( $lines );
        $groupsTxt = array_pop($lines);
        $groups = explode( " ", $groupsTxt );
        return $groups;
    }

    protected function isValidCommand( $text ){
        return preg_match( "/\\!|;|\\$|&|<|>|`|'|\"]/", $text ) == 0;
    }
}