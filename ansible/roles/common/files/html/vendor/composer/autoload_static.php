<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit91e8e2f90315615ee1ddb91f997f0a2e
{
    public static $files = array (
        'decc78cc4436b1292c6c0d151b19445c' => __DIR__ . '/..' . '/phpseclib/phpseclib/phpseclib/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'p' => 
        array (
            'phpseclib\\' => 10,
        ),
        'D' => 
        array (
            'Delight\\I18n\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'phpseclib\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpseclib/phpseclib/phpseclib',
        ),
        'Delight\\I18n\\' => 
        array (
            0 => __DIR__ . '/..' . '/delight-im/i18n/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit91e8e2f90315615ee1ddb91f997f0a2e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit91e8e2f90315615ee1ddb91f997f0a2e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit91e8e2f90315615ee1ddb91f997f0a2e::$classMap;

        }, null, ClassLoader::class);
    }
}