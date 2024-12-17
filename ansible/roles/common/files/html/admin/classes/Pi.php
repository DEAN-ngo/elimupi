<?php

class Pi extends Command{

    use cmd;

    function commandDiskUsage(){

        $res = $this->cmd( 'df ' );
        $str = $res['str'];

        if( ! empty( $str )){
            $mediaLines = explode( "\n", $str );
            $dfs = array( );

            // If there are warnings the first lines needs to be skipped
            foreach( $mediaLines as $n => $line ){
                if( substr( $line, 0, 10 ) == 'Filesystem'){
                    break;
                }
                else{
                    array_shift( $mediaLines );
                }
            }
            
            forEach( $mediaLines as $line ){

                if( ! empty( $line )){
                   $tabs = $this->parseDfOutput( $mediaLines[ 0 ] . " " . $line);
                    if( $tabs[ 'result' ][ 'Mounted_on' ] == "/mnt/content-1" ||
                    $tabs[ 'result' ][ 'Mounted_on' ] == "/mnt/content" ||
                        $tabs[ 'result' ][ 'Mounted_on' ] == "/")
                        array_push( $dfs, $tabs);
                }
            }

            $res = $this->cmd( "lsblk -p -P --output NAME,MOUNTPOINT,TYPE,SIZE" );
            $ls = $res['str'];
            if( $res['exit'] == 0){
                $csv = CSV::parseAsCSV( $ls, " ");
                $unmounted = array();

                // Find unmounted partitions
                foreach( $csv['data'] as $l ){
                        if( $l[1] == 'MOUNTPOINT=""'
                            && $l[2] == 'TYPE="part"'
                            ){
                            $parts = explode('"', $l[0] );
                            $parts2 = explode('"', $l[3] );
                            $dev['mnt'] = $parts[1];
                            $dev['result'] = array( 
                                'unmounted' => true,
                                'blocks' => $parts2[1],
                                'Used' => 0, 
                                'Used%' => 0
                            );
                            $dfs[] = $dev;
                        }
                }
            }

            return $dfs;   
        }
        else{
            Log::writeLine( "User " . $this->user . " cannot view disk usage." );
            return array( "msg" => "Error" );
        }
    }

    private function parseDfOutput( $str ){
        $str = str_replace( "Mounted on", "Mounted_on", $str);
        $tabs = preg_split('/\s{1,}/', $str);
        $ar = array( 
            'mnt' => $tabs[ 11 ], 
            'result' => array( 
                $tabs[ 1 ] => $tabs[ 7 ],
                $tabs[ 2 ] => $tabs[ 8 ],
                $tabs[ 3 ] => $tabs[ 9 ],
                $tabs[ 4 ] => $tabs[ 10 ],
                $tabs[ 5 ] => $tabs[ 11 ],
                'unmounted' => false
            )
        );

        return $ar;
    }

    function commandEject( $mountPoint ){

        if( $this->isValidCommand( $mountPoint ))
            $res = $this->cmd( "sudo umount -l $mountPoint" );

        return array( "msg" => $res['str'], "mnt" => $mountPoint );
    }

    function commandRunLevel6(){
        
        $this->cmd( 'sleep(2); sudo shutdown -r now' );

        // If all is well $str == false, otherwise it indicates some error
        return array( "msg" => '' );
    }

    function commandRunLevel0(){
        
        // Add a slleep(2) so this request can be finished
        $str = $this->cmd( 'sudo shutdown -H now' );

        if( $res['exit'] == 0 )
            return array( "msg" => false );
        else
            return array( 'msg' => _('Something went wrong.'));
    }

    function mountDisk( $dev ){
        if( $this->mountContentDisk( $dev ))
            return array( 'msg' => _('OK'), 'ok' => true );
        else
            return array( 'msg' => _('Something went wrong.'), 'ok' => false );
    }

    function mountContentDisk( $dev ){
        $grps = $this->getUserGroups();
        if( in_array( 'sudo', $grps ) ){
    
            // Mount it as user pi so www-data can read/write and make sure the root folder is present
            $res = $this->cmd( "ls /mnt/content-1" );
            if( $res['exit'] > 0 )
                $this->cmd( "sudo mkdir /mnt/content-1" );
            $res = $this->cmd("sudo mount -o umask=0000,gid=1000,uid=1000 " . $dev . " /mnt/content-1");
  
            if( $res['exit'] == 0 )
                return true;
            else
                return false;
        }
        else
            return false;
    }
}