<?php

class Users extends Command{
    

    use cmd;

    function getUsersByGroup( $type ){

        $users = array();
        $groups = explode( "|", $type );

        $res = $this->cmd( 'getent passwd' );
        $usersText = $res['str'];

        $userTextRaw = explode( "\n", $usersText );
        foreach( $userTextRaw as $line ){
            $user = explode( ":", $line )[ 0 ];
            if( empty( $user )) break;

            $res = $this->cmd( 'groups ' .  $user );
            $lines = explode("\n", $res['str']);
            array_pop($lines);
            $groupsOutput = str_replace( "\n", "", array_pop($lines));
            $userGroupsRaw = explode( ":", $groupsOutput );
            if( count( $userGroupsRaw ) > 1 )
                $userGroups = explode( " ", $userGroupsRaw[ 1 ] );

            foreach( $groups as $group ){
                // If a user is both a student and a teacher he will be listed as the first $type
                if( in_array( $group, $userGroups )){
                    $u = array( 'user' => $user, 'group' => $group );
                    array_push( $users, $u );
                    break;
                }
            }
        }

        return $users;
    }

    function commandLogin( $username ){
        return array( 'username' => $username, 'groups' => $this->getUserGroups());
    }

    // Resets password of specified user. New password twice.
    function commandResetPassword( $user, $newPassword ){

        $in = "sudo " . scriptsLocation . "/changepassword.sh $user << EOD\n$newPassword\n$newPassword\nEOD";
        $res = $this->cmd( $in );
        
        if( $res['exit'] == 0 ){
            $groups = $this->getUserGroups();
            $ldapOk = false;
            if( in_array( 'teachers', $groups ))
                $ldapOk = LDAP::updatePassword( 'teachers', $user, $newPassword );
            else if( in_array( 'students', $groups ))
                $ldapOk = LDAP::updatePassword( 'students', $user, $newPassword );

            if( $ldapOk )
                return array( 'msg' => $this->parsePasswordChangeOutput( $res['str'] ));
            else
                return array( 'msg' => $this->parsePasswordChangeOutput( $res['str'] ) . " LDAP error." );
        }
        else
            return;
    }

    // Changes password for current user. First old password, second new password
    function commandChangePassword( $password, $newPassword ){
        $username = explode( "\n", $this->cmd( 'whoami' )['str']);
        if( ! in_array( 'pi', $username )){
            $in = "passwd << EOD\n$password\n$newPassword\n$newPassword\nEOD";
            $res = $this->cmd( $in );
    
            $ok = $res['exit'] == 0;
            $str = $res['str'];
            
            $groups = $this->getUserGroups();
            $user = array_pop( $username );
            $ldapGrp = '';
            $ldapOk = false;
            
            if( preg_match("/.*password updated successfull.*/i", $res['str']) > 0 ){
                if( in_array( 'students', $groups ))
                    $ldapGrp = 'students';
                else if( in_array( 'teachers', $groups ))
                    $ldapGrp = 'teachers';

                if( $ldapGrp == 'students' || $ldapGrp == 'teachers' )
                    $ldapOk = LDAP::updatePassword( $ldapGrp, $user, $newPassword );
            }

            if( $ldapOk )
                return array( 'msg' => $this->parsePasswordChangeOutput( $str ), 'ok' => $ok);
            else
                return array( 'msg' => $this->parsePasswordChangeOutput( $str ) . " LDAP error.", 'ok' => $ok);
        }
        else
            return array( 'msg' => "Operation not allowed.", "ok" => false );
    }

    function commandAddUser( $userName, $password, $type ){

        $status = 'error';
        $UN = new UserName( $userName );
        $userName2 = $UN->get();

        $base1 = 'sudo ' . scriptsLocation . '/addstudent.sh ';
        $base2 = 'sudo ' . scriptsLocation . '/addteacher.sh ';
        $cmd = "$userName2 '$password'";
        $exit = -1;

        if( $type == 'students')
            $in = $base1 . $cmd;
        else if( $type == 'teachers')
            $in = $base2 . $cmd;

        if( $this->isValidCommand( $userName2 )){
            $res = $this->cmd( $in );
            $str = str_replace( "\n", "", $res['str']);
            $exit = $res['exit'];
        }
        else
            $str = "Invalid command";

        if( empty( $str ) & $UN->isConverted() ){
            $status = 'ok';
            $str = _f( '%1$s has been converted to %2$s. Account created succesfully.', $userName, $userName2 );
        }
        else if( $exit == 0 ){
            $status = 'ok';
            $str = _f( 'Account for %1$s has been created successfully.', $userName );
        }
        else{
            if( preg_match( '/already exists/', $str ))
                $str = _f( 'Account for %1$s already exists.', $userName );
            else
                $str = _( 'Something went wrong.' );
        }

        $ldapOk = null;
        if( $status == 'ok' && $exit == 0 )
            $ldapOk = LDAP::addAccount( $type, $userName2, $password );

        if( is_null( $ldapOk ) || $ldapOk)
            return array( 'status' => $status, 'msg' => $str );
        else
            return array( 'status' => $status, 'msg' => $str . " LDAP error.");
    }

    function commandDeleteUser( $user ){

        $exit = -1;
        if( $this->isValidCommand( $user )){
            $res = $this->cmd( "sudo " . scriptsLocation . "/deleteuser.sh $user $this->user" );
            $exit = $res['exit'];
        }

        if( $exit == 0 ){
            LDAP::deleteAccount( 'students', $user );
            LDAP::deleteAccount( 'teachers', $user );
            return array( 'msg' => "Removed " . $user . "." );
        }
        else{
            Log::writeLine( "Delete user $user failed by " . $this->user );
            return array( 'msg' => _('Something went wrong.'));
        }
    }

    function commandListUsers( $type ){

        $users = $this->getUsersByGroup( $type );

        return array( "users" => $users );
    }

    private function parsePasswordChangeOutput( $str ){
        $result = explode( "Retype new password:", $str );

        if( count( $result ) > 1 )
            array_splice( $result, 0, 1);

        $str = explode( "Password change", $result[ 0 ] )[ 0 ];

        $strA = explode( "\n", $str );

        $ret = array();

        foreach( $strA as $line ){
            if( preg_match( "/too similar/", $line ))
                array_push( $ret,  _( 'Password is too similar.' ));
            else if( preg_match("/too short/", $line ))
                array_push( $ret, _('Password is too short.'));
            else if( preg_match( "/the same/", $line ))
                array_push( $ret, _( 'Password is the same.' ));
            else if( preg_match( '/too simple/', $line ))
                array_push( $ret, _( 'The new password is too simple.' ));
            else if( preg_match( '/Password is unchanged/', $line ))
                array_push( $ret, _( 'Password is unchanged.' ));
            else if( preg_match( '/new password is just a wrapped/', $line ))
                array_push( $ret, _( 'Password is like the old one.' ));
            else if( preg_match( '/password updated successfully/', $line ))
                array_push( $ret, _( 'Password updated successfully.'));
            else if( preg_match(  '/Changing password for/', $line ))
                ;
            else if( preg_match( '/New password/', $line ))
                ;
            else if( ! empty( $line )){
                Log::writeLine( $line );
                if( ! in_array( _('Something went wrong.'), $ret ))
                    array_push( $ret, _('Something went wrong.') );
            }
            if( preg_match( "/New password/", $line ))
                array_push( $ret, _( 'Try again.' ));
        }

        return implode( "\n", $ret );
    }

}