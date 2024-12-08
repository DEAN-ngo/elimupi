<?php

class Install extends Command{

    use cmd;

    function installMoodleLDAPPlugin(){
        $ok = false;
        $enabled = '';
        $plugin = __DIR__ . '/files/auth_ldap.xml';

        $res = $this->moosh( "config-plugin-import  $plugin" );
        $ok = $res['exit'] == 0;
        if( $ok ){
            $this->moosh( "auth-manage enable ldap" );
            $this->moosh( "cache-clear" );
            $res = $this->moosh( "auth-list" );
            if( $res['exit'] == 0 )
                $enabled = $res['str'];
        }

        return array( 'msg' => $ok ? $enabled : _('Something went wrong.'), 'ok' => $ok );
    }

    /*
        Make $dir an hidden directory within $packagesDir
    */
    private function removeFromPackages( $packagesDir, $dir ){
        $hids = glob( $packagesDir . '.' . $dir . '*', GLOB_ONLYDIR );
        $ns = [0];
        foreach( $hids as $fn){
            $d = str_replace($packagesDir.".", "", $fn);
            preg_match("/^\{[A-Z0-9-]*\}[-](\d+)$/", $d, $m);
            if( $m && ! empty( $m[1]) )
                $ns[] = intval( $m[1] );
        }
        $n = 1 + max( $ns );
        
        shell_exec("mv $packagesDir$dir $packagesDir.$dir-$n");
    }

    private function addToPackages( $source, $id, $updatePack ){
        $packagesDir = PackagesHelper::getPackagesDir();
        
        // If it is an update make old directory hidden
        if( !empty( $updatePack ) && is_dir( $packagesDir . $updatePack)){
            self::removeFromPackages( $packagesDir, $updatePack );
            clearstatcache();
        }
        
        if( ! is_dir( $packagesDir . $id ))
            shell_exec("mv $source $packagesDir$id");
    }

    function commandUploadContent(){
        $ok = false;
        $isPublic = $_POST['public'] === 'on';
        $updatePack = null;
        if( ! empty($_POST['packageId']))
            $updatePack = stripslashes( strip_tags( $_POST['packageId']));
        $title = null;
        if( ! empty($_POST['title']))
            $title = stripslashes( strip_tags( $_POST['title'] ));

        if( ! empty( $_FILES['zip'] )){
            $dest = '/tmp/' . uniqid() . '/';
            $fn = $_FILES['zip']['name'];
            mkdir( $dest );
            
            if( is_uploaded_file( $_FILES['zip']['tmp_name'] ) 
                && move_uploaded_file( $_FILES['zip']['tmp_name'], $dest . $fn)){
                
                if( PackagesHelper::isAPKFile( $dest . $fn )){
                    $a = PackagesHelper::upgradeDirectoryToPackage( $dest, $this->user, $isPublic, $title, $updatePack );
                    if( $this->installAPK( $dest, $_FILES['zip']['name'] )){
                        $ok = true;
                        $this->addToPackages( $dest, $a['id'], $updatePack );
                        PackagesHelper::generatePackagesXml(PackagesHelper::getPackagesDir());
                    }
                }

                else if( $this->isZIPFile( $dest . $fn )){
                    if( $dir = $this->unzip( $dest . $fn )){
                        
                        // Check if it is one directory
                        $dirs = glob( $dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
                        if( count($dirs) == 1)
                            $dir = $dirs[0];

                        if( $a = PackagesHelper::upgradeDirectoryToPackage( $dir, $this->user, $isPublic, $title, $updatePack )){
                            switch( $a['type'] ){
                                case 'Apk':
                                    $apkFns = glob( "$dir/*.[aA][pP][kK]" );
                                    foreach( $apkFns as $apkFn )
                                        $res = $this->installAPK( $dir, $apkFn );
                                break;
                            }
                            $this->addToPackages( $dir, $a['id'], $updatePack );
                            $ok = true; 
                            PackagesHelper::generatePackagesXml( PackagesHelper::getPackagesDir());
                        }
                    }
                }
           }
        }

        if(! $ok )
            return array('msg' => _('Something went wrong.'), 'ok' => $ok);        
        else
            return array('msg' => _('OK'), 'ok' => true );
    }

    private function installAPK( $source, $fn ){
        copy( $source . $fn, localPath . "/fdroid/repo/" . $fn);        
        $res = $this->cmd( "cd " . localPath . "/fdroid/; sudo fdroid update --create-metadata --rename-apks  --allow-disabled-algorithms" );
        return $res['exit'] == 0;
    }

    function commandInstallPackages( $packages, $path ){
        $types = array();

        foreach( $packages as $package ){
            if( ! isset( $types[ $package->type ] ))
                $types[ $package->type] = array();
            array_push( $types[ $package->type ], $package );
        }

        foreach( $types as $type => $packages ){
            switch( $type ){
                case 'Apk':
                    $this->installAPKPackages( $packages, $path, localPath . '/fdroid/');
                break;

                case 'Moodle':
                    $this->installMoodlePackages( $packages, $path );
                break;
            }
        }
        return array('msg' => 'ok', 'ok' => true);
    }

    private function installMoodlePackages( $packages, $path ){

        foreach( $packages as $package ){

            $textPackage = file_get_contents( $path . $package->uniqueId . "/index.xml" );
            $xml = new SimpleXMLElement( $textPackage );

            // The xml format doesn't yet exist on the content side
            // Assuming analogy with APK package
            $mfile = (string)$xml->moodleFileName;

            if( is_dir( $path . $package->uniqid )){
                $this->installMoodlePackage( $mfile, $path . $xml->uniqId );
            }
        }
    }

    private function installMoodlePackage( $moodleFile, $path ){
        $tempDir = '/tmp/moodlepackage';

        $tgz = preg_replace( "/[.]mbz$/", ".tgz", $moodleFile );
        $tar = preg_replace( "/[.]mbz$/", ".tar", $moodleFile );
        
        if( is_dir( $tempDir ))
            shell_exec( "rm -Rf " . $tempDir );
        mkdir( $tempDir );
        copy( $path . $moodleFile, "$tempDir/$tgz" );
        chdir( $tempDir );
        shell_exec( "gunzip $tempDir/$tgz" );

        if( is_file( $tempDir . '/' . $tar )){
            shell_exec( "tar xvf $tempDir/$tar" );
            $id = '';
            $category = '';

            $courseXml = "$tempDir/course/course.xml";
            if( is_file( $courseXml )){

                $xml = new SimpleXMlElement( file_get_contents( $courseXml ));
                $id = (string)$xml->idnumber;
                $category = (string)$xml->category->name;

                // Search idnumber to see if already installed:
                // idnumber should be provided with the course creation
                $res = $this->moosh( 'course-list -n --output=csv' );
                $csv = CSV::parseAsCSV( $res['str'] );

                $found = false;
                foreach( $csv['data'] as $n => $row ){
                    $idnumber = $row[ array_search( "idnumber", $csv['columns']) ];
                    if( $idnumber == $id ){
                        $found = $row[ array_search( 'id', $csv['columns'] )];
                    }
                }

                // category ID
                $res = $this->moosh( 'category-list --output=csv' );
                $csv = CSV::parseAsCSV( $res['str'] );

                $categoryId = null;
                foreach( $csv['data'] as $row ){
                    $cat = $row[ array_search( 'name', $csv['columns'] )];
                    if( $cat == $category )
                        $categoryId = $row[ array_search( 'id', $csv['columns'] )];
                }

                // Create category if needed
                if( $categoryId === null ){
                    $res = $this->moosh( "category-create $category" );
                    $categoryId = $res['str'];
                }

                if( $found !== false ){
                    // Update existing course
                    $this->moosh( "course-restore -e --ignore-warnings $path/$moodleFile $found " );
                }
                else{
                     // Install new course
                    $cat = $categoryId ? $categoryId : $misc;
                    $this->moosh( "course-restore --ignore-warnings $path/$moodleFile $categoryId" );
                }
            }
        }

        // Clean up 
        shell_exec( 'rm -Rf /tmp/moodlepackage' );
    }

    private function installAPKPackages( $packages, $path, $toPath ){

        foreach( $packages as $package ){
            if( is_dir( $path. $package->uniqueId )){
                $textPackage = file_get_contents( $path . $package->uniqueId . "/index.xml" );
                $xml = new SimpleXMLElement( $textPackage );
    
                $fileName = (string)$xml->APKFileName;
    
                copy( "$path$package->uniqueId/$fileName",  $toPath . '/repo/' . uniqid() . '.apk');
            }
        }

        $this->cmd( "cd $toPath; sudo fdroid update --create-metadata --rename-apks  --allow-disabled-algorithms" );
    }

    private function unzip( $file ){
        $dest = '/tmp/local-package/';
        shell_exec("rm -Rf $dest");
        mkdir($dest);
        $zip = new ZIPArchive();
        $fh = $zip->open( $file );
        if( $fh === true )
            if( $zip->extractTo( $dest ))
                return $dest;
        else
            return false;
    }

    private function isZIPFile( $file ){
        $mimes = array(
            'application/zip'
        );
        return in_array( mime_content_type( $file ), $mimes);
    }

}