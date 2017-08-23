<?php
use PHPUnit\Framework\TestCase;

require '../../../bin/controller.php';

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $conf1 = new \zaek\kernel\CConfig(['user' => ['id' => 1]]);
        $this->expectException(\zaek\kernel\CException::class);
        $conf1->getValue('user', 'name');
    }
}