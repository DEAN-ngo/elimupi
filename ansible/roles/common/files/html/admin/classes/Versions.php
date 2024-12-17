<?php

class Versions extends Command{

    use cmd;

    function getVersions(){
        $i = array(
            "pi" => 'error',
            "moodle" => 'error', 
            "moosh" => 'error',
            "kolibri" => 'error', 
            "kiwix" => 'error', 
            "fdroid" => 'error',
            "php" => 'error',
            "python2" => 'error',
            "python3" => 'error');
        $ret = $i;

        foreach( $i as $k => $p ){
            switch( $k ){

                case 'pi':
                    $grps = $this->getUserGroups();
                    if( in_array( 'sudo', $grps )){
                        $res = $this->cmd('sudo cat /proc/cpuinfo | grep Model');
                        if( $res['exit'] == 0 ){
                            $lines = explode("\n", $res['str']);
                            $ss = explode(":", $lines[0]);
                            $ret['pi'] = $ss[1];
                        }
                    }
                    else
                        $ret['pi'] = '-';
                break;
                
                case 'moodle':
                    $res = $this->moosh('-v');
                    $mvTxt = $res['str'];
                    if( $mvTxt ){
                        $lines = explode("\n", $mvTxt);
                        $ret['moodle'] = $this->getLastWord($lines[0]);
                        $ret['moosh'] = $this->getLastWord($lines[1]);
                    }
                break;
                
                case 'kolibri':
                    $res = $this->cmd('kolibri --version');
                    if( $res['exit'] == 0 )
                        $ret['kolibri'] = $this->getLastWord($res['str']);
                break;

                case 'kiwix':
                    $res = $this->cmd('/var/kiwix/bin/kiwix-read --version');
                    if( $res['exit'] == 0 ){
                        $t = explode( "\n", $res['str'] );
                        $ret['kiwix'] = $this->getLastWord( $t[0] );
                    }
                break;

                case 'fdroid':
                    $res = $this->cmd('fdroid --version');
                    if( $res['exit'] == 0 )
                        $ret['fdroid'] = $res['str'];
                break;

                case 'php':
                    $ret['php'] = phpversion();
                break;

                case 'python2':
                    $res = $this->cmd('python --version');
                    if( $res['exit'] == 0)
                        $ret['python2'] = explode(" ", $res['str'])[1];
                break;

                case 'python3':
                    $res = $this->cmd('python3 --version');
                    if( $res['exit'] == 0)
                        $ret['python3'] = explode(" ", $res['str'])[1];
                break;
            }
        }

        return array("msg" => $ret);
    }

    private function getLastWord($line){
        $wrds = explode(" ", $line);
        return array_pop($wrds);
    }
}