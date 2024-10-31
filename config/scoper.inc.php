<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

// You can do your own things here, e.g. collecting symbols to expose dynamically
// or files to exclude.
// However beware that this file is executed by PHP-Scoper, hence if you are using
// the PHAR it will be loaded by the PHAR. So it is highly recommended to avoid
// to auto-load any code here: it can result in a conflict or even corrupt
// the PHP-Scoper analysis.

return [
    // The prefix configuration. If a non null value is be used, a random prefix
    // will be generated instead.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#prefix
    'prefix' => null,

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // This configuration entry is completely ignored when using Box.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#finders-and-paths
    'finders' => [
		// WP-Background-Processing
		Finder::create()->files()->in( 'vendor/deliciousbrains/wp-background-processing' )->name( [ '*.php', 'license.txt', 'composer.json' ] ),
		// Guzzle
		Finder::create()->files()->in( 'vendor/guzzlehttp' )->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		// mustache
		Finder::create()->files()->in( 'vendor/mustache/mustache' )->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		// prs
		Finder::create()->files()->in( 'vendor/psr/container' )->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		Finder::create()->files()->in( 'vendor/psr/http-client' )->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		Finder::create()->files()->in( 'vendor/psr/http-factory' )->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		Finder::create()->files()->in( 'vendor/psr/http-message' )->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		Finder::create()->files()->in( 'vendor/psr/log' )->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
		// algolia
		Finder::create()->files()->in( 'vendor/algolia/algoliasearch-client-php' )->name( [ '*.php', 'LICENSE', 'composer.json' ] ),
    ],

    // List of excluded files, i.e. files for which the content will be left untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#patchers
    'exclude-files' => [
        //'src/a-whitelisted-file.php',
    ],

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#patchers
    'patchers' => [
        static function (string $filePath, string $prefix, string $contents): string {
            // Change the contents here.
			// replace KLASS and KLASS_NO_LAMBDAS Mustache_Template for StaticSnapVendor\Mustache_Template
			if (  false !== strpos( $filePath, 'mustache/mustache/src/Mustache/Compiler.php' )) {
				$contents = str_replace( 'class %s extends Mustache_Template', 'class %s extends StaticSnapVendor\Mustache_Template', $contents );
			}
			// wp-async-request.php
			if (  false !== strpos( $filePath, 'deliciousbrains/wp-background-processing/classes/wp-async-request.php' )) {
				// replace alias \class_alias('StaticSnapVendor\\WP_Async_Request', 'WP_Async_Request', \false);
				$contents = str_replace( "\\class_alias('StaticSnapVendor\\\\WP_Async_Request', 'WP_Async_Request', \\false);", "\\class_alias('StaticSnapVendor\\\\WP_Async_Request', 'StaticSnapVendor_WP_Async_Request', \\false);", $contents );


			}
			if (  false !== strpos( $filePath, 'deliciousbrains/wp-background-processing/classes/wp-background-process.php' )) {
				// also in background-processing.php
				$contents = str_replace( "\\class_alias('StaticSnapVendor\\\\WP_Background_Process', 'WP_Background_Process', \\false);", "\\class_alias('StaticSnapVendor\\\\WP_Background_Process', 'StaticSnapVendor_WP_Background_Process', \\false);", $contents );
			}
			if (  false !== strpos( $filePath, 'algolia/algoliasearch-client-php/src/Http/GuzzleHttpClient.php' )) {
				// use non deprecated class.
				$contents = str_replace( "\\StaticSnapVendor\\GuzzleHttp\\choose_handler", "\\StaticSnapVendor\\GuzzleHttp\\Utils::chooseHandler",$contents );

			}

			//vendor_prefixed/algolia/algoliasearch-client-php/src/SearchIndex.php
			if (  false !== strpos( $filePath, 'algolia/algoliasearch-client-php/src/SearchIndex.php' )) {
				$contents = str_replace( "api_path", "Helpers::apiPath",$contents );
			}


            return $contents;
        },
    ],

    // List of symbols to consider internal i.e. to leave untouched.
    //
    // For more information see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#excluded-symbols
    'exclude-namespaces' => [
        // 'Acme\Foo'                     // The Acme\Foo namespace (and sub-namespaces)
        // '~^PHPUnit\\\\Framework$~',    // The whole namespace PHPUnit\Framework (but not sub-namespaces)
        // '~^$~',                        // The root namespace only
        // '',                            // Any namespace
    ],
    'exclude-classes' => [
        // 'ReflectionClassConstant',
    ],
    'exclude-functions' => [
        // 'mb_str_split',
    ],
    'exclude-constants' => [
        // 'STDIN',
    ],

    // List of symbols to expose.
    //
    // For more information see: https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#exposed-symbols
    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,
    'expose-namespaces' => [
        // 'Acme\Foo'                     // The Acme\Foo namespace (and sub-namespaces)
        // '~^PHPUnit\\\\Framework$~',    // The whole namespace PHPUnit\Framework (but not sub-namespaces)
        // '~^$~',                        // The root namespace only
        // '',                            // Any namespace
    ],
    'expose-classes' => [],
    'expose-functions' => [],
    'expose-constants' => [],
];
