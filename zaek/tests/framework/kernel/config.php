<?php
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testUnsetValue()
    {
        // Config
        $conf1 = new \zaek\kernel\CConfig(['user' => ['id' => 1]]);
        $this->expectException($conf1->getValue('user', 'name'));
    }
}