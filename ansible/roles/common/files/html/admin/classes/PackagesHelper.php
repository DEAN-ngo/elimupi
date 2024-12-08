<?php

class PackagesHelper{

    static private function globPackages( $path ){
        return glob( $path . '/[!' . notVerifiedCharacter . ']*', GLOB_ONLYDIR );
    }

    static function getPackagesDir(){
        return localPath . '/' . localRepo ;
    }

    // Generate target repository packages.xml
    static function generatePackagesXml( $path ){ 
        $text = '';

        $dirs = self::globPackages( $path );

        foreach( $dirs as $dir ){
            $t = file_get_contents( $dir . '/index.xml' );
            if( $t !== false ){
                $l = explode( "\n", $t );
                array_shift( $l );
                $text .= implode( "\n", $l );
            }
        }

        $text = str_replace( '<Info>', '<Package>', $text );
        $text = str_replace( '</Info>', '</Package>', $text );

        if( !empty( $text )){
            libxml_use_internal_errors( true );
            try{
                $xml = new SimpleXMLElement( "<Packages>" . $text . "</Packages>");
                $xml->asXML( $path . '/packages.xml' );
            }
            catch( Exception $e ){}
        }
    }

    static function hasSecondaryDisk(){
        return is_dir( '/mnt/content-1/Content' );
    }

    // Create Packages folder if needed
    static public function initRepo( $path ){
        if( ! is_dir( $path )){
            mkdir( $path );
        }

        return is_dir( $path );
    }

    static function isPackage( $url, $package ){
        $check = true;
        $needed = array( "files.xml", "index.xml" );

        if( is_dir( $url )){
            foreach( $needed as $file ){
                if( ! is_file( $url . '/' . $package . '/' . $file))
                    $check = false;
            }
            return $check;
        }
        else{
            foreach( $needed as $file ){
                $exists = self::fileExists(
                    PackagesHelper::toFileUrl( $url . $package . '/' . $file )
                );
                if( ! $exists )
                    $check = false;
            }
            return $check;
        }
    }

    static function getFromPackageXml( $path, closure $xmlFunc ){
        $index = file_get_contents( $path . DIRECTORY_SEPARATOR . 'index.xml');
        $xml = new SimpleXMLElement( $index );
        return call_user_func( $xmlFunc, $xml );
    }

    static function getPackageAccessibility( $path ){
        return self::getFromPackageXml( $path, function( $xml ){ return (string)$xml->Accessibility[0]; });
    }

    static function getPackageType( $path ){
        return self::getFromPackageXml( $path, function( $xml ){ return (string)$xml->Type[0]; });
    }

    static function getPackageDescriptionByLang( $path, $lang ){
        return self::getFromPackageXml( $path, function( $xml ) use ($lang){ 
            $descriptions = $xml->Descriptions->Description;
            $fnd = '';

            // Try local language 
            foreach( $descriptions as $d )
                if( self::getNSAttribute( $d ) == $lang)
                    $fnd = $d;

            // Else English?
            if( empty( $fnd )){
                foreach( $descriptions as $d )
                    if( self::getNSAttribute( $d ) == 'en')
                        $fnd = $d;
            }
            return (string)$fnd; 
        });
    }

    static function getPackageUniqueId( $path ){
        return self::getFromPackageXml( $path, function( $xml ){ return (string)$xml->UniqueId[0]; });
    }

    static function getPackageDescription( $path, $lang = 'en' ){
        return self::getFromPackageXml( $path, function( $xml ){ return (string)$xml->Descriptions[0]->Description[0]; });
    }

    // Workaround packages xml doesn't have namespace for lang attribute (xml:lang)
    // This works because there is only one such attribute
    static function getNSAttribute( $tag ){
        foreach( $tag->attributes('xml', true) as $att ){
            $a = (array)$att;
            return $a[0];
        }
    }

    static function getLanguagesFromPackageXml( $path ){
        return self::getFromPackageXml( $path, function( $xml ){
            $xmlLangs = [];
            $tags = $xml->Descriptions->Description;
            foreach( $tags as $tag )
                $xmlLangs[] = self::getNSAttribute( $tag );
            return $xmlLangs;
        });
    }
    
    static function packageDirectoryExists( $id ) : bool{
        return is_dir( localPath . localRepo . $id );
    }

    static function verifySizes( $dir ) : bool{
        $check = true;
        $tags = self::getFilesXML( $dir );
        foreach( $tags as $file ){
            $ar = (array)$file;
            $fn = str_replace('\\', '/', $ar[0]);

            $localSize = is_file( $dir . '/' . $fn ) ? filesize( $dir . '/' . $fn ) : 0;
            $remoteSize = (int)$ar['@attributes']['size'];

            if( $localSize != $remoteSize )
                $check = false;
        }
        return $check;
    }

    static private function generatePackageIdLocalContent(){
        if (function_exists('com_create_guid')){
            $uid = com_create_guid();
        }
        else {
            mt_srand((double)microtime()*10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = chr(123) // "{"
                . substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid,12, 4) . $hyphen
                . substr($charid,16, 4) . $hyphen
                . substr($charid,20,12)
                . chr(125); // "}"
            $uid = $uuid;
        }
        return $uid;
    }

    static function isLocalContent( $packageId ){
        return preg_match( '/^\{[A-Z0-9]{8}[-][A-Z0-9]{4}[-][A-Z0-9]{4}[-][A-Z0-9]{4}[-][A-Z0-9]{12}\}$/', $packageId ) === 1; 
    }

    static function getAllLocalPackageIds(){
        $ret = [];
        $packagesDir = self::getPackagesDir();
        $packs = glob( $packagesDir . '[!' . notVerifiedCharacter . ']*');
        foreach( $packs as $p )
            $parts = explode(DIRECTORY_SEPARATOR, $p);
            $d = array_pop($parts);
            if( self::isLocalContent( $d )){
                $title = self::getPackageDescriptionByLang( $p, Language::getLanguageCode());
                $ret[] = array( $title => $d );
            }

        return $ret;
    }

    static function initDomDoc(){
        $dom = new DOMDocument();
        $dom->encoding = 'utf-8';
		$dom->xmlVersion = '1.0';
		$dom->formatOutput = true;
        return $dom;
    }

    static private function writeFilesXml( $path ){
        $dom = self::initDomDoc();
        
        $fn = 'files.xml';
        $root = $dom->createElement( 'Files' );

        $files = glob( $path . '*' );
        $files[] = 'files.xml';
        foreach( $files as $file ){
            $size = '';
            if( is_file( $file ))
                $size = filesize( $file );
            $f = $dom->createElement( 'File', str_replace($path, "", $file ));
            $f->setAttribute( 'size', $size );
            $root->appendChild( $f );
        }
        $dom->appendChild($root);
        $size = $dom->save( $path . $fn );

        // Reload to adjust filesize files.xml, the last element in the file
        $dom->load( $path . $fn );
        $fs = $dom->getElementsByTagName('File');
        $delta = strlen("$size");
        $fs->item( count( $fs ) - 1)->setAttribute( 'size', $size + $delta );

        $dom->save( $path . $fn );
    }

    static function updateIndexXml( $path, $id, $author, $size, $public, $addedDirs ){
        $dom = self::initDomDoc();
        $dom->load( $path . 'index.xml' );

        $u = $dom->getElementsByTagName('UniqueId');
        $u->item(0)->nodeValue = $id;

        $a = $dom->getElementsByTagName('Creator');
        $a->item(0)->nodeValue = $author;

        $s = $dom->getElementsByTagName('Size');
        $s->item(0)->nodeValue = $size;

        $p = $dom->getElementsByTagName('Accessibility');
        $p->item(0)->nodeValue = $public? 'Public' : 'Private';

        $d = $dom->getElementsByTagName('Descriptions')[0];
        $h = $dom->getElementsByTagName('HtmlIndexFiles')[0];
        foreach( $addedDirs as $langDir ){
            $dd = $dom->createElement('Description', 'translation??');
            $dd->setAttribute('xml:lang', $langDir);
            $d->appendChild($dd);

            $hi = $dom->createElement('HtmlIndexFile', $langDir . '/index.html');
            $hi->setAttribute('xml:lang', $langDir);
            $h->appendChild($hi);
        }

        $dom->save( $path . 'index.xml' );
    }

    static private function writeIndexXml( $type, $path, $id, $author, $public, $title, $size, $htmlIndexFile, $version = 1){
        global $i18n;

        $dom = self::initDomDoc();

        $fn = 'index.xml';
        $root = $dom->createElement('Info');

        $root->appendChild( $dom->createElement( 'UniqueId', $id ));
        $root->appendChild( $dom->createElement( 'Type', $type ));
        $root->appendChild( $dom->createElement( 'Accessibility', $public? 'Public' : 'Private' ));
        $root->appendChild( $dom->createElement( 'ContentVersion', $version ));

        $descrs = $dom->createElement( 'Descriptions' );
        $descr = $dom->createElement( 'Description', $title );
        $descr->setAttribute('xml:lang', substr( $i18n->getLocale(), 0, strpos( $i18n->getLocale(), '-') ));
        $descrs->appendChild($descr);
        $root->appendChild( $descrs );

        if( $type == 'Html'){
            $index = $root->appendChild( $dom->createElement( 'HtmlIndexFiles' ));
            $ni = $dom->createElement( 'HtmlIndexFile', $htmlIndexFile);
            $ni->setAttribute('xml:lang', Language::getLanguageCode());
            $index->appendChild( $ni );
            $root->appendChild( $index );
        }
        else if( $type == 'Apk' ){
            $root->appendChild( $dom->createElement( 'APKFileName', $htmlIndexFile ));
        }

        $source = $dom->createElement( 'Source' );
        $source->appendChild( $dom->createElement( 'Creator', $author ));
        $source->appendChild( $dom->createElement( 'Application', 'ElimuGo' ));
        $source->appendChild( $dom->createElement( 'Device', 'local' ));
        $root->appendChild( $source );

        $root->appendChild( $dom->createElement( 'Size', $size ));
        $root->appendChild( $dom->createElement( 'ReleaseDate', date('Y-m-d\TH:i:sO')));
        $dom->appendChild($root);

        $dom->save( $path . $fn );

    }

    static private function makeXmlFiles( $type, $author, $path, $id, $title, $size, $public, $index = 'index.html' ){
        $knownTypes = array( 'Html', 'Apk' );
        if( in_array( $type, $knownTypes )){
            self::writeIndexXml( $type, $path, $id, $author, $public, $title, $size, $index );
            self::writeFilesXml( $path );
            return $id;
        }
    }

    static private function updateXmlFiles( $type, $author, $path, $id, $size, $public, $addedDirs ){
        $knownTypes = array( 'Html', 'Apk' );
        if( in_array( $type, $knownTypes )){
            self::updateIndexXml( $path, $id, $author, $size, $public, $addedDirs );
            self::writeFilesXml( $path );
            return $id;
        }
    }

    static function dirSize($directory) {
        $size = 0;
        if( is_dir( $directory ))
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
                $size+=$file->getSize();
            }
        return $size;
    } 

    static function upgradeDirectoryToPackage( $path, $author = 'Dean', $public = false, $title = null, $updateOf = null ){

        $type = '';
        $id = '';
        
        if( substr( $path, strlen( $path ) - 1, 1) !== DIRECTORY_SEPARATOR )
            $path .= DIRECTORY_SEPARATOR;

        $dirParts = explode( DIRECTORY_SEPARATOR, $path );
        array_pop( $dirParts );
        $packageName = array_pop( $dirParts );
        $d = implode( DIRECTORY_SEPARATOR, $dirParts );
        $size = self::dirSize( $path );

        // If it is a package overwrite its xml files
        // and give it a local uniqueId
        // to allow local modifications of public packages
        // or locally generated content
        if( self::isPackage( $d, $packageName )){
            
            if( self::getPackageAccessibility( $path ) == 'Public' ||
                self::isLocalContent( $packageName ) ){

                $type = self::getPackageType( $path );
                $uniqueId = is_null( $updateOf )? self::generatePackageIdLocalContent() : $updateOf;
                
                // check if folders were added that should be in de xml (e.g. translation)
                $langs = [];
                $xmlLangs = self::getLanguagesFromPackageXml( $path );

                $dirs = glob( $path . '*', GLOB_ONLYDIR );
                foreach( $dirs as $dir )
                    if( $lng = self::isLangDir( $dir ))
                        if( $lng )
                            $langs[] = $lng;

                $addedDirs = array_diff( $xmlLangs, $langs );
                $id = self::updateXmlFiles( $type, $author, $path, $uniqueId, $size, $public, $addedDirs );            
            }
        }    

        // Check if root dir is Html
        else if( self::isHtmlDir( $path ) ){
            $type = 'Html';

            // Check language, prefix it to the index and move files to this folder
            $lang = Language::getLanguageCode();
            if( ! is_dir( $path . $lang )){
                $files = glob($path . '*');
                mkdir( $path . $lang );
                foreach( $files as $file ){
                    $f = str_replace($path, "", $file);
                    rename( $file, $path . $lang . DIRECTORY_SEPARATOR . $f);
                }
            }

            // If ok upgrade it with package title as the name of the folder in the ZIP file if not provided
            if( is_null($title) )
                $title = $packageName;

            $packageName = is_null( $updateOf )? self::generatePackageIdLocalContent() : $updateOf;
                
            if( is_file( $path . $lang . DIRECTORY_SEPARATOR . 'index.html'))
                $id = self::makeXmlFiles( 'Html', $author, $path, $packageName, $title, $size, $public, $lang . DIRECTORY_SEPARATOR . 'index.html' );
        }

        // If it is a collection of Pdfs
        else if( self::isPdfDir( $path ) ){
            // ...
        }
        
        // If it isn't an Html or Pdf dir try Apk
        else if( self::isApkDir( $path ) ){
            $type = 'Apk';
            $fileName = '';

            $files = glob( $path . '*.[aA][pP][kK]');
            foreach( $files as $file )
                if( self::isAPKFile( $file ))
                    $fileName = str_replace($path, "", $file);

            if( is_null($title) )
                $title = $packageName;

            $packageName = is_null( $updateOf )? self::generatePackageIdLocalContent() : $updateOf;
            
            $id = self::makeXmlFiles( 'Apk', $author, $path, $packageName, $title, $size, $public, $fileName );
        }

        // Otherwise make it an indexed Html if ok
        else{
            $dirs = glob( $path . '/*', GLOB_ONLYDIR );
            $isLang = false;
            $isHtml = false;
            $isPdf = false;
            foreach( $dirs as $dir ){
                $isLang = self::isLangDir( $dir );
                $isHtml = self::isHtmlDir( $dir );
                $isPdf = self::isPdfDir( $dir );
    
                // ...

            //    var_dump(  $dir, $isLang, $isHtml, $isPdf );
            }
        }

        if( ! empty( $type ) && ! empty( $id ))
            return array(
                "type" => $type,
                "id" => $id
            );
        return false;
    }

    static private function isLangDir( $path ){
        $lang = array(
            'en',
            'nl',
            'sw',
            'am'
        );
        $name = '';
        $l = explode( DIRECTORY_SEPARATOR, $path );
        if( count( $l ) > 0 )
            $name = array_pop( $l );
        if(in_array( $name, $lang ))
            return $name;
        return false;
    }

    static private function isTypeDir( $path, bool $onlyOneFile, closure $typeFunc ){
        $files = glob( $path . '*' );
        $isType = count( $files ) > 0;

        if( $onlyOneFile && count( $files ) > 1)
            return false;

        foreach( $files as $file ){
            if( ! call_user_func( $typeFunc, $file ))
                $isType = false;
        }
        return $isType;
    }

    static private function isHtmlDir( $path ){
        return self::isTypeDir( $path, false, function( $file ){ return self::isHtmlFile( $file ); } );
    }

    static private function isPdfDir( $path ){
        return self::isTypeDir( $path, false, function( $file ){ return self::isPdfFile( $file ); } );
    }

    static private function isApkDir( $path ){
        return self::isTypeDir( $path, true, function( $file ){ return self::isApkFile( $file ); } );
    }

    static private function isHtmlFile( $file ){
        $mimes = array(
            'text/plain',
            'text/html',
            'text/css',
            'image/bmp',
            'image/gif',
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/svg+xml',
            'application/pdf'
        );
        return in_array( mime_content_type( $file ), $mimes);
    }

    static function isAPKFile( $file ){
        $mimes = array(
            'application/vnd.android.package-archive',
            'application/octet-stream',
            'application/java-archive'
        );
        return in_array( mime_content_type( $file ), $mimes);
    }

    static private function isPdfFile( $file ){
        $mimes = array(
            'application/pdf'
        );
        return in_array( mime_content_type( $file ), $mimes);
    }
    
    static function toFileUrl( $url ){
        $parts = parse_url($url);
        $path_parts = array_map('rawurldecode', explode('/', $parts['path']));
        
        return $parts['scheme'] . '://' . $parts['host'] . implode('/', array_map('rawurlencode', $path_parts));
    }

    static function fileExists( $url ){
        if( extension_loaded( 'curl' )){
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_NOBODY, true);
            curl_exec( $ch );
            $result = curl_getinfo( $ch,  CURLINFO_HTTP_CODE );
            curl_close( $ch );
            return (int)$result == 200;
        }
        return true;
    }

    private static function getFilesXML( $dir ){
        $text = file_get_contents( "$dir/files.xml" );
        
        // Strip anything before start xml tag
        return new SimpleXMLElement( substr( $text, strpos( $text, '<?xml')) );
    }
}