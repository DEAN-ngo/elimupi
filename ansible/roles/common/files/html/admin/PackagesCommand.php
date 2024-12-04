<?php

class PackagesCommand{

    function __construct( $req, $var ){

        $packages = new Packages();
        $resp = array();

        switch( $req ){

            /*case 'hasPackagesSecondaryDisk':
                $packages->hasSecondaryDiskJson();
            break;*/

            case 'getPackagesAvailableSources':
                $resp = $packages->getAvailableSources();
            break;
            
            case 'getPackagesXml':
                echo $packages->getIndexXml( localPath . localRepo . 'packages.xml' );
                die;
            break;

            case 'newPackages':
                if( $var['elimuGo'] == 'yes' )
                    if( PackagesHelper::fileExists(elimuGoUrl . remoteFolder . 'packages.xml'))
                        $resp = $packages->commandNewPackages( elimuGoUrl . remoteFolder . 'packages.xml', localPath . localRepo, $var['lang'] );
                    else
                        $resp = $packages->commandNewPackages( elimuGoUrl . 'packages.xml', localPath . localRepo, $var['lang'] );
                else if( PackagesHelper::hasSecondaryDisk())
                    $resp = $packages->commandNewPackages( localPath2 . localRepo2 . 'packages.xml', localPath . localRepo, $var['lang'] );
                else
                    $resp = $packages->commandNewPackages( elimuPiUrl . remoteFolder . 'packages.xml', localPath . localRepo, $var['lang'] );
            break;

            case 'copyPackages':
                if( $var['elimuGo'] == 'yes' )
                    if( PackagesHelper::fileExists(elimuGoUrl . remoteFolder . 'packages.xml'))
                        $resp = $packages->commandCopyPackages( json_decode( $var['packages'] ), elimuGoUrl . remoteFolder );
                    else
                        $resp = $packages->commandCopyPackages( json_decode( $var['packages'] ), elimuGoUrl );
                else if( PackagesHelper::hasSecondaryDisk())
                    $resp = $packages->commandCopyPackages( json_decode( $var['packages'] ), localPath2 . localRepo2  );
                else
                    $resp = $packages->commandCopyPackages( json_decode( $var['packages'] ), elimuPiUrl . remoteFolder );
            break;

            case 'verifyPackages':
                if( PackagesHelper::hasSecondaryDisk())
                    $resp = $packages->commandVerifyPackages( json_decode( $var['packages'] ), localPath2 . localRepo2 );
                else
                    $resp = $packages->commandVerifyPackages( json_decode( $var['packages'] ), elimuPiUrl . remoteFolder );

                PackagesHelper::generatePackagesXml( localPath . localRepo );
            break;

            case 'copyPackagesToSecondaryDisk':
                $resp = $packages->commandCopyPackagesToDisk( json_decode( $var['packages'] ));
            break;

            case 'getAllPackagesForSelectionCopy':
                $resp = $packages->commandGetAllPackagesForSelection( $var['targetDisk'], $var['lang']);
            break;

            case 'getLocalPackagesIds':
                $resp['packages'] = PackagesHelper::getAllLocalPackageIds();
            break;

        }

        if( ! is_null( $resp ))
            new Response( $resp );
        else
            new Reponse( array( 'msg' => _('Something went wrong.'), 'ok' => false ));
    }
}
?>