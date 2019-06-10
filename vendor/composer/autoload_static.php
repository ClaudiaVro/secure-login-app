<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb4c33f070f4f758f92dea708b9be3143
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb4c33f070f4f758f92dea708b9be3143::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb4c33f070f4f758f92dea708b9be3143::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
