<?php

class SSHCommand extends SSHCommandBase{
    
    use cmd;
    
    function __construct( $req, $username, $password, $var = null ){
        
        // The base class does the login
        parent::__construct( $username, $password );

        $resp = array();

        switch( $req  ){

            case 'login':
                $users = new Users( $this->ssh, $this->user );
                $resp = $users->commandLogin( $username );
            break;

            case 'resetPassword':
                $users = new Users( $this->ssh, $this->user );
                $resp = $users->commandResetPassword( $var[ 'forUser' ], $var[ 'newPassword' ]);
            break;

            case 'changePassword':
                $users = new Users( $this->ssh, $this->user );
                $resp = $users->commandChangePassword( $var[ 'password' ], $var[ 'newPassword' ]);
            break;

            case 'addUser':
                $users = new Users( $this->ssh, $this->user );
                $resp = $users->commandAddUser( $var[ 'userName' ], $var[ 'newPassword' ], $var[ 'type' ]);
            break;

            case 'listUsers':
                $users = new Users( $this->ssh, $this->user );
                $resp = $users->commandListUsers( $var[ 'types' ]);
            break;

            case 'deleteUser':
                $users = new Users( $this->ssh, $this->user );
                $resp = $users->commandDeleteUser( $var[ 'userName' ]);
            break;

            case 'diskUsage':
                $pi = new Pi($this->ssh, $this->user);
                $resp = $pi->commandDiskUsage();
            break;

            case 'ejectMedia':
                $pi = new Pi($this->ssh, $this->user);
                $resp = $pi->commandEject( $var[ 'which' ] );
            break;

            case 'install':
                $ins = new Install( $this->ssh, $this->user );
                $resp = $ins->commandInstallPackages( json_decode( $var['packages']), localPath . localRepo );
            break;

            case 'uploadZIP':
                $ins = new Install( $this->ssh, $this->user );
                $resp = $ins->commandUploadContent();
            break;

            case 'installMoodleLDAPPlugin':
                $ins = new Install( $this->ssh, $this->user );
                $resp = $ins->installMoodleLDAPPlugin();
            break;

            case 'runLevel6':
                $pi = new Pi($this->ssh, $this->user);
                $resp = $pi->commandRunLevel6();
            break;

            case 'runLevel0':
                $pi = new Pi($this->ssh, $this->user);
                $resp = $pi->commandRunLevel0();
            break;

            case 'mountDisk':
                $pi = new Pi($this->ssh, $this->user);
                $resp = $pi->mountDisk( $var['which'] );
            break;

            case 'createContentDisk':
                $cd = new ContentDisk( $this->ssh, $this->user );
                $resp = $cd->createContentDisk( );
            break;

            case 'downloadLogs':
                $adm = new AdminFunctions( $this->ssh, $this->user );
                $resp = $adm->downloadLogs();
            break;

            case 'createBackup':
                $adm = new AdminFunctions( $this->ssh, $this->user );
                $resp = $adm->backupUserAccounts();
            break;

            case 'restoreBackup':
                if( is_uploaded_file( $_FILES['backup']['tmp_name'] ) && 
                    move_uploaded_file( $_FILES['backup']['tmp_name'], "/tmp/backup.tar.gz")){
                        $adm = new AdminFunctions( $this->ssh, $this->user );
                        $resp = $adm->restoreUserAccounts("/tmp/backup.tar.gz");
                    }
            break;

            case 'getInstalledVersions':
                $ver = new Versions( $this->ssh, $this->user );
                $resp = $ver->getVersions();
            break;
        }

        if( ! is_null( $resp ))
            new Response( $resp );
        else
            new Response( array( 'msg' => _('Something went wrong.'), 'ok' => false ));
    }

}

?>