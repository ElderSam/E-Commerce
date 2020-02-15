<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitce8225833e7f7c0f26572328bda20bbb
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Eldersam\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Eldersam\\' => 
        array (
            0 => __DIR__ . '/..' . '/eldersam/php-classes/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'Slim' => 
            array (
                0 => __DIR__ . '/..' . '/slim/slim',
            ),
        ),
        'R' => 
        array (
            'Rain' => 
            array (
                0 => __DIR__ . '/..' . '/rain/raintpl/library',
            ),
        ),
    );

    public static $classMap = array (
        'EasyPeasyICS' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/EasyPeasyICS.php',
        'PHPMailer' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
        'PHPMailerOAuth' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauth.php',
        'PHPMailerOAuthGoogle' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauthgoogle.php',
        'POP3' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.pop3.php',
        'SMTP' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.smtp.php',
        'ntlm_sasl_client_class' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/ntlm_sasl_client.php',
        'phpmailerException' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitce8225833e7f7c0f26572328bda20bbb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitce8225833e7f7c0f26572328bda20bbb::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitce8225833e7f7c0f26572328bda20bbb::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitce8225833e7f7c0f26572328bda20bbb::$classMap;

        }, null, ClassLoader::class);
    }
}
