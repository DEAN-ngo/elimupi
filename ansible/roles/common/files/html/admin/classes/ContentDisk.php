<?php

class ContentDisk extends Command{

    use cmd;

    private $label = '';

    private $path = '';

    private $subPath = array();

    private $foldersCreated = 0;

    private $foldersSkipped = 0;

    function createContentDisk(){
        $text = file_get_contents( 'xml/content-disk.xml' );
        if( !empty( $text )){
            $xml = new SimpleXMLElement( $text );
            $folders = $xml->xpath( '//volume/folder' );
            $volumeLabelXml = $xml->xpath( '//volume' );
            $this->label = (string)$volumeLabelXml[0]->attributes()['label'];
            $hasContentDisk = is_dir( '/mnt/' . $this->label );
            if( ! $hasContentDisk || empty( $this->label ))
                return array( 'msg' => "No volume with label '$this->label' could be found." );
            else{
                $this->createFolders( $folders );
                return array( 'msg' => "Folders created: " . $this->foldersCreated . ", folders already existing: " . $this->foldersSkipped );
            }
        }
    }

    private function createFolders( $folders ){
        foreach( $folders as $folder ){
            $name = (string)$folder->attributes()['name'];
            $permissions = (string)$folder->attributes()['permissions'];
            $this->createRootFolder( $name, $permissions, $folder );
        }
    }

    private function getPath(){
        return $this->path . '/' . implode( "/", $this->subPath );
    }

    private function createRootFolder( $name, $permissions, $folder ){
        $this->path = "/mnt/$this->label" ;

        array_push( $this->subPath, $name);

        $files = (array)$folder->xpath( 'file' );
        $this->_createFolder( $this->getPath(), $permissions, $files );

        if( isset( $folder->folder))
        foreach( $folder->folder as $child ){
            $subName = (string)$child->attributes()['name'];
            $permissions = (string)$child->attributes()['permissions'];
            $files = (array)$child->xpath( 'file' );
            $this->createFolder( $permissions, $subName, $child->folder, $files );
        }

        array_pop( $this->subPath );
    }

    private function createFolder( $permissions, $name, $childs, $files ){
        array_push( $this->subPath, $name );
        $path = $this->getPath();
        $this->_createFolder( $path, $permissions, $files );
        if( $childs )
        foreach( $childs as $child ){
            $this->createFolder( 
                (string)$child->attributes()['permissions'], 
                $child->attributes()['name'], 
                $child->folder,
                (array)$child->xpath( 'file' )
            );
        }
        array_pop( $this->subPath );
    }

    private function _createFolder( $path, $permissions, $files ){
        if( ! is_dir( $path )){
            mkdir( $path );
            //chmod( $path, octdec($permissions ));
            if( is_dir( $path )){
                if( $path == "/mnt/$this->label/fdroid" ){
                    $this->cmd("cd $path; sudo fdroid init");
                }
                foreach( $files as $file ){
                    $fileName = (string)$file->attributes()['src'];
                    $permissions = (string)$file->attributes()['permissions'];
                    $fn = explode( '/', $fileName );
                    $p = explode( '/', $path );
                    $root = array_pop( $p ); 
                    $f = array_pop( $fn );
                    copy( getcwd() . "/xml/$fileName", $path . "/$f" );
                    //chmod( $path . "/$f", octdec( $permissions ));
                }
                $this->foldersCreated++;
            }
        }
        else
            $this->foldersSkipped++;
    }
}