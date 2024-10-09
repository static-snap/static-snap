<?php

$composer_autoloader_file = __DIR__ . '/../vendor/autoload.php';

if ( ! file_exists( $composer_autoloader_file ) ) {
	die( 'Installing composer are required for running the tests.' );
}

require $composer_autoloader_file;


$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( getenv( 'WP_PHPUNIT__DIR' ) ) {
	$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
}

require_once $_tests_dir . '/tests/phpunit/includes/functions.php';
require $_tests_dir . '/tests/phpunit/includes/bootstrap.php';




