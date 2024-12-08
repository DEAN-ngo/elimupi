<?php

class Packages{
    
    function getAvailableSources(){
        return array( 
            "ok" => true,
            "msg" => '',
            "elimuGo" => PackagesHelper::fileExists( elimuGoUrl ),
            "secondDisk" => PackagesHelper::hasSecondaryDisk(),
            'numberOfPackages' => $this->numberOfInstalledPackages( localPath . localRepo )
        );
    }

    /*function hasSecondaryDiskJson(){
        if( $this->hasSecondaryDisk( ))
            $a = 'yes';
        else
            $a = 'no';

        new Response( 
            array( 
                'msg' => $a,
                'numberOfPackages' => $this->numberOfInstalledPackages( localPath . localRepo )
            ) 
        );
    }*/

    // Get the local repository index.xml. Used by requests from the interface.
    function getIndexXml( $urlOrPath ){
        $text = file_get_contents( $urlOrPath );
        if( $text !== false )
            return $text;
        else
            return "<errors><error>" . _( 'Local packages list is not readable.' ) . "</error><errors>";
    }

    function commandGetAllPackagesForSelection( $targetDisk, $preferedLang ){
        $names = array();
        if( empty( $targetDisk ))
            $targetDisk = $this->getTargetDisk( );

        if( ! empty( $targetDisk ) && is_dir( $targetDisk )){
            return $this->commandNewPackages( localPath . localRepo . 'packages.xml', $targetDisk . '/Content/' . rootFolder . '/', $preferedLang );
        }
        else
            return array( 'status' => 'error', 'msg' => 'No target disk.');
    }

    function commandCopyPackagesToDisk( $packages ){
        $copied = 0;
        $targetDisk = $this->getTargetDisk( );
        if( !empty( $targetDisk )){
            foreach( $packages as $name ){

                $targetRoot = $targetDisk . 'Content/' . rootFolder;
                $target = "/$targetRoot/$name";

                $exists = is_dir( $target );
                if( $exists ){
                    $cmd = 'rm -Rf ' . $target;
                    `$cmd`; 
                }
                $cmd = 'cp -Rp ' . localPath . localRepo . $name . ' ' . $target;
                `$cmd`;
                $copied++;
            }
            PackagesHelper::generatePackagesXml( $targetRoot );
            return array( 'msg' => 'Packages copied: ' . $copied . '.');
        }
        else
            return  array( 'msg' => 'No target disk' );
    }

    // Compare repo at $formUrlOrPath with local repo and reply the number of new packages
    function commandNewPackages( $fromUrlOrPath, $targetPath, $preferedLang ){

        if( PackagesHelper::initRepo( $targetPath ) ){
            
            $newPackages = array();
            $updatePackages = array();

            $tags = $this->getSourceIndexXml( $fromUrlOrPath );
            $localXml = false;
            if( is_file( $targetPath . 'packages.xml' ))
                $localXml = file_get_contents( $targetPath . 'packages.xml');
    
            $localTags = null;
            if( $localXml !== false )
                $localTags = $this->parseTextAsXml( $localXml );
    
            $tagsRemote = $tags->xpath( '/Packages/Package' );
    
            foreach( $tagsRemote as $key => $item ){

                $accessibility = (array)$item->xpath("Accessibility");
                if( $accessibility[0] != 'Public')
                    continue;

                $package = (array)$item->xpath( 'UniqueId' );
                $uniqueid = (array)$package[0];

                $local = false;
                if( $localXml )
                    $local = (array)$localTags->xpath( "//UniqueId[. ='". $uniqueid[0] . "']/parent::*" );

                $descr = $item->xpath( "Descriptions/Description[@xml:lang='$preferedLang']" );
                if( empty( $descr[0] )){
                    $descr = $item->xpath( "Descriptions/Description[@xml:lang='en']" );
                    $description = (string)$descr[0];
                }
                else{
                    $description = (string)$descr[0];
                }

                if( empty( $local ) )
                    array_push( $newPackages, array( 
                        'uniqueId' => (string)$item->UniqueId, 
                        'type' => (string)$item->Type,
                        'description' => $description ) 
                    );
                else if( (string)$local[0]->ReleaseDate < (string)$tagsRemote[$key]->ReleaseDate )
                    array_push( $updatePackages, array( 
                        'uniqueId' => (string)$item->UniqueId, 
                        'type' => (string)$item->Type,
                        'description' => $description )
                    );
                
            }
    
            return array(
                'status' => 'ok', 
                'msg' => '', 
                'new' => count( $newPackages ), 
                'newPackages' => $newPackages,
                'updates' => count( $updatePackages ),
                'updatePackages' => $updatePackages 
            );
        }
        else
            return array( 'status' => 'error', 'msg' => 'Error: ' . _( 'ElimuPi disk not available.' ));
    }

    // Copy packages from $fromUrlOrPath to local repo and reply number of packages copied
    function commandCopyPackages( $packages, $fromUrlOrPath ){
        $copied = 0;
        foreach( $packages as $package ){

            $id = $package->uniqueId;

            if( PackagesHelper::isPackage( $fromUrlOrPath, $id )){
                $tags = $this->getFilesXML( $fromUrlOrPath, $id );
                $files = $tags->xpath( 'File' );
    
                // Make an folder with start _ to indicate it is not verified yet;
                $dir = localPath . localRepo . notVerifiedCharacter . $id;
                if( ! is_dir( $dir )){
                    mkdir( $dir );
                }
    
                foreach( $files as $file ){
    
                    $ar = (array)$file;
                    $ar[0] = str_replace('\\', '/', $ar[0]);
                    
                    // Only fetch files which are smaller or don't exist
                    $localSize = is_file( $dir . '/' . $ar[0] ) ? filesize( $dir . '/' . $ar[0] ) : 0;
                    
                    $remoteSize = (int)$ar['@attributes']['size'];
                    if( $localSize < $remoteSize ){

                        // HTTP requests for filename with spaces etc need this
                        if( ! is_dir( $fromUrlOrPath ))
                            $url = PackagesHelper::toFileUrl( $fromUrlOrPath . $id . '/' . $ar[ 0 ] );
                        else
                            $url = $fromUrlOrPath . $id . '/' . $ar[ 0 ];

                        $fileContent = false;
                        $rFile = null;
                        if( ! is_dir( $fromUrlOrPath ) &&  PackagesHelper::fileExists( $url )){
                            $rFile = fopen( $url, 'r' );
                            while( true ){
                                
                                $offset = $localSize + strlen( $fileContent );

                                $chunk = stream_get_contents( $rFile, chunkSize, $offset );
                                
                                // Error?
                                if( $chunk === false )
                                    break;

                                // Finished?
                                else if( strlen( $chunk ) < chunkSize ){
                                    $fileContent .= $chunk;
                                    break;
                                }
                                else
                                    $fileContent .= $chunk;
                            }
                            fclose( $rFile );
                        }
                        else if( file_exists( $url ))
                            $fileContent = file_get_contents( $fromUrlOrPath . $id . '/' . $ar[ 0 ] );
    
                        if( $fileContent !== false ){
                            $path = explode( "/", $ar[0]);
                            if( count( $path ) > 1){
                                array_pop( $path );
                                foreach( $path as $k => $p ){
                                    $cdir = implode( "/", array_slice( $path, 0, $k + 1 ));
                                    if( ! is_dir( $dir . '/' . $cdir ))
                                        mkdir( $dir . '/' . $cdir );
                                }
                            }
                            $appendTo = false;
                            if( file_exists( $dir . '/' . $ar[0]))
                                $appendTo = file_get_contents( $dir . '/' . $ar[0]);
                            if( $appendTo !== false )
                                $fileContent = $appendTo . $fileContent;
                            file_put_contents( $dir . '/' . $ar[0], $fileContent );
                        }
                    }
                }
                $copied++;
            }
        }

        return array( 'msg' => false, 'copied' => $copied );
    }

    // Verify the packages $packages in local repo with the one at $fromUrlOrPath
    function commandVerifyPackages( $packages, $fromUrlOrPath ){
        $verified = 0;

        foreach( $packages as $package ){

            $id = $package->uniqueId;
            $dir = localPath . localRepo . notVerifiedCharacter . $id;

            if( is_dir( $dir )){

                if(  PackagesHelper::verifySizes( $dir ) ){

                    // If there is a directory already there is also an update
                    if( PackagesHelper::packageDirectoryExists( $id ))
                        system("rm -rf ". escapeshellarg(localPath . localRepo . $id ));

                    // Strip the prefix of the folder name to indicate it is verified
                    rename( $dir,  localPath . localRepo . $id );
                    $verified++;
                }
            }
        }

        return array( 'msg' => false, 'verified' => $verified );
    }

    private function getTargetDisk( ){
        $path = '';
        for( $n = 8; $n > 0; $n-- ){
            if( is_dir( '/mnt/content-' . $n)){
                $path = '/mnt/content-' . $n . '/';
            }
        }
        return $path;
    }

    private function numberOfInstalledPackages( $urlOrPath ){
        $n = 0;
        $text = false;
        if( is_file( $urlOrPath . 'packages.xml'))
            $text = file_get_contents( $urlOrPath . 'packages.xml');
        if( $text !== false ){
            $xml = simplexml_load_string( $text );
            $n = count( $xml->xpath('Package'));
        }
        return $n;
    }

    private function getSourceIndexXml( $fromUrlOrPath ){
        if( is_file( $fromUrlOrPath ) || PackagesHelper::fileExists($fromUrlOrPath)){
            $text = file_get_contents( $fromUrlOrPath );
            if( $text !== false )
                return $this->parseTextAsXml( $text );
        }
        else
            $this->errorResponse( 'Error : could not read from ' . $fromUrlOrPath );
    }

    private function getFilesXML( $urlOrPath, $package ){

        $text = file_get_contents( $urlOrPath . $package . '/files.xml' );

        if( $text !== false )
            return $this->parseTextAsXml( $text );
        
        else
            $this->errorResponse( 'Error : could not read ' . $package . ' from ' . $urlOrPath );
    }

    private function parseTextAsXml( $text ){

        // Strip anything before start xml tag
        $text = substr( $text, strpos( $text, '<?xml'));

        $tags = new SimpleXMLElement( $text );

        if( empty( $tags ))
            $this->errorResponse( 'Error: could not parse xml.' );
        else
            return $tags; 
    }

    private function errorResponse( $str ){
        new Response( array( 'msg' => $str ));
        die;
    }
}