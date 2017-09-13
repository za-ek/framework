<?php
namespace PHPUnit\Framework;

define('TESTING', true);

include_once __DIR__ . '/../bin/controller.php';

try {
    if (class_exists('PHPUnit_Framework_TestCase')) {
        class TestCase extends \PHPUnit_Framework_TestCase {

        }
    }
} catch ( Exception $e ) {

}