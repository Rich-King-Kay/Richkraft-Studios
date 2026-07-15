<?php
/**
 * PHPUnit bootstrap.
 *
 * Starts a session before any application code runs so that config.php
 * (which calls session_start()) does not emit warnings under the CLI SAPI,
 * then loads the Composer autoloader.
 */

error_reporting(E_ALL & ~E_DEPRECATED);

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
