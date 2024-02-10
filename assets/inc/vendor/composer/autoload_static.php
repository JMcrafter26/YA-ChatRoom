<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit56616780f377289e7fe2e37115b926d7
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'IconCaptcha\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'IconCaptcha\\' => 
        array (
            0 => __DIR__ . '/..' . '/fabianwennink/iconcaptcha/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit56616780f377289e7fe2e37115b926d7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit56616780f377289e7fe2e37115b926d7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit56616780f377289e7fe2e37115b926d7::$classMap;

        }, null, ClassLoader::class);
    }
}
