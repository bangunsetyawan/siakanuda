<?php

/**
 * SIAKANUDA Dashboard — Front Controller (Entry Point)
 * 
 * File ini adalah satu-satunya file yang dapat diakses secara publik.
 * Semua request akan diarahkan ke sini oleh Nginx/.htaccess.
 */

// Check PHP version.
if (version_compare(PHP_VERSION, '8.1', '<')) {
    exit(sprintf('Your PHP version must be 8.1 or higher to run CodeIgniter. Current version: %s', PHP_VERSION));
}

// Path to the front controller (paths acceptable to require_once)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
chdir(__DIR__);

// Load our paths config file
$pathsConfig = realpath(FCPATH . '../app/Config/Paths.php');

if ($pathsConfig === false || !is_file($pathsConfig)) {
    header('HTTP/1.1 503 Service Unavailable', true, 503);
    echo 'Error: File konfigurasi Paths.php tidak ditemukan. Pastikan CodeIgniter 4 terinstall dengan benar.';
    exit(1);
}

require $pathsConfig;

$paths = new Config\Paths();

define('COMPOSER_PATH', realpath(FCPATH . '../vendor/autoload.php'));

// Load the framework bootstrap file
require $paths->systemDirectory . '/Boot.php';

// Boot the application!
CodeIgniter\Boot::bootWeb($paths);
