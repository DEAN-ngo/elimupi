<?php

class AdminFunctions extends Command{

    use cmd;

    function downloadLogs(){

        $this->cmd( "rm -f /tmp/logs.tar.gz; tar -zcvf /tmp/logs.tar.gz " . localPath . '/www_log/' );

        return array( 'msg' => $this->makeDownloadLink( 'logs.tar.gz' ), 'ok' => true );
    }

    /**
     * Make a backup of user accounts
     * https://www.cyberciti.biz/faq/howto-move-migrate-user-accounts-old-to-new-server/
     */
    function backupUserAccounts(){
        
        // Check if user has root access
        $groups = $this->getUserGroups();
        if( in_array( "sudo", $groups )){

            // Get folder to store backup in
            if( is_dir( "/tmp/backup" ))
                $this->cmd( "rm -Rf /tmp/backup/*" );
            else
                $this->cmd( "mkdir -p /tmp/backup" );

            // Copy user accounts (but not the pi account uid == 1000)
            $this->cmd( "sudo awk -v LIMIT=1001 -F: '($3>=LIMIT) && ($3!=29999)' /etc/passwd > /tmp/backup/passwd.mig" );

            // Copy group file
            $this->cmd( "sudo awk -v LIMIT=1001 -F: '($3>=LIMIT) && ($3!=29999)' /etc/group > /tmp/backup/group.mig" );

            // Copy shadow passwd
            $this->cmd( "sudo awk -v LIMIT=1001 -F: '($3>=LIMIT) && ($3!=29999) {print $1}' /etc/passwd | tee - |sudo egrep -f - /etc/shadow > /tmp/backup/shadow.mig" );

            // Copy other shadow
            $this->cmd( "sudo cp /etc/gshadow /tmp/backup/gshadow.mig" );

            // Backup LDAP
            $this->cmd("sudo slapcat > /tmp/backup/ldap.ldif");

            // Copy README
            $this->cmd( "sudo cp " . __DIR__ . "/../files/readme-backup.txt /tmp/backup/readme.txt" );

            // Zip folder
            `tar -zcvf /tmp/backup.tar.gz /tmp/backup`;

            return array( 'msg' => $this->makeDownloadLink( 'backup.tar.gz' ), 'ok' => true );

        }
        else{
            // Not enough rights
            return null;
        }
    }

    private function makeDownloadLink( $file ){
        return '/admin/request.php?download=' . $file;
    }
    
    function restoreUserAccounts( $targz ){
        $skipped = 0;
        $restored = 0;
        $groups = $this->getUserGroups();
        if( in_array("sudo", $groups)){
            $this->cmd( "rm -Rf /tmp/backuprestore" );
            $this->cmd( "mkdir /tmp/backuprestore" );
            $this->cmd( "tar xvfz $targz --directory /tmp/backuprestore" );

            // The linux user password files
            $base = "/tmp/backuprestore/tmp/backup";
            $files = array( "passwd.mig", "shadow.mig", "group.mig" );
            foreach($files as $file){
                $fileNameParts = explode( ".", $file );
                $text = "$base/$file"; 
                $exFile = $fileNameParts[0];
                if(file_exists( "/etc/$exFile" ) && file_exists($text)){
                    $csvExist = CSV::parseAsCsv(file_get_contents("/etc/$exFile"), ":", false);
                    $csv = CSV::parseAsCSV(file_get_contents($text), ":", false);
                    foreach($csv['data'] as $line){
                        if( ! $this->entryExists( $csvExist['data'], $line[0])){
                            $ln = implode(":", $line);
                            $this->cmd( "sudo chmod a+w /etc/$exFile" );
                            $this->cmd( "sudo echo \"$ln\" >> /etc/$exFile" );
                            $this->cmd( "sudo chmod a-w /etc/$exFile" );
                            $restored++;
                        }
                        else{
                            $skipped++;
                        }
                    }
                }
            }

            if(file_exists("/etc/gshadow") && file_exists("$base/gshadow.mig")){
                $this->cmd( "sudo cp $base/gshadow.mig /etc/gshadow" );
            }

            // Restore LDAP (existing LDAP should hold the main Elimupi configuration. Existing entries will not get modified.)
            if(file_exists("/usr/bin/ldapadd")){
                $restored++;
                $this->cmd( "sudo ldapadd -f $base/ldap.ldif -x -D 'cn=Manager,dc=elimupi,dc=local' -w elimupi -c" );
            }
            else
                $skipped++;
            
            $msg = "OK";
            $all = $restored + $skipped;
            if($skipped > 0)
                $msg = "Some entries were skipped ($skipped / $all)";
               
            return  array("msg" => $msg, "ok" => true);
        }
        else{
            // Not enough rights
            return null;
        }
    }

    private function entryExists($csv, $name ){
        foreach($csv as $value){
            if($value[0] == $name)
                return true;
        }
        return false;
    }
}
