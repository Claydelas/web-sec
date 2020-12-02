<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit043cbf4ae0508c49f665d1a5973c08c0
{
    public static $files = array (
        '45e8c92354af155465588409ef796dbc' => __DIR__ . '/..' . '/bcosca/fatfree/lib/base.php',
        '2cffec82183ee1cea088009cef9a6fc3' => __DIR__ . '/..' . '/ezyang/htmlpurifier/library/HTMLPurifier.composer.php',
    );

    public static $prefixesPsr0 = array (
        'H' => 
        array (
            'HTMLPurifier' => 
            array (
                0 => __DIR__ . '/..' . '/ezyang/htmlpurifier/library',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit043cbf4ae0508c49f665d1a5973c08c0::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
