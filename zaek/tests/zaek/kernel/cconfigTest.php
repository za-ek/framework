<?php
use PHPUnit\Framework\TestCase;

class cconfigTest extends TestCase
{
    protected $_config;
    protected $_a_config;
    protected function setUp()
    {
        $this->_a_config = ['user' => ['id' => 1]];
        $this->_config = new \zaek\kernel\CConfig($this->_a_config);
    }
    public function testIsDefined()
    {
        $this->assertEquals(false, $this->_config->isDefined('user', 'name'));
        $this->assertEquals(true, $this->_config->isDefined('user', 'id'));
    }
    public function testGetArray()
    {
        $this->assertEquals($this->_a_config, $this->_config->getArray());
    }
    public function testGetValue()
    {
        $this->assertEquals(1, $this->_config->getValue('user','id'));

        $this->expectException(\zaek\kernel\CException::class);
        $this->_config->getValue('user', 'name');
    }
    public function testOverride()
    {
        $conf = clone $this->_config;
        $conf->override(['user' => ['id' => 2, 'name' => 'Me']]);
        $this->assertEquals(2, $conf->getValue('user','id'));
        $this->assertEquals('Me', $conf->getValue('user','name'));
    }
}