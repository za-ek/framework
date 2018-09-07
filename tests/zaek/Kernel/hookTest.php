<?php
use PHPUnit\Framework\TestCase;

function returnFunction ($a)
{
    return $a;
}

class StaticTestClass {
    public static function test($param) {
        return $param;
    }
    public static function throwTest()
    {
        throw new Exception('', 1);
    }
};

class hookTest extends TestCase
{
    protected function setUp()
    {
        \Zaek\Kernel\Hooks::bind(['test', 'test1'], function($param) {return $param;});
        \Zaek\Kernel\Hooks::bind(['test', 'test2'], [new class {public function a($param){return $param;}}, 'a']);
        \Zaek\Kernel\Hooks::bind(['test', 'test3'], ['StaticTestClass', 'test']);
        \Zaek\Kernel\Hooks::bind(['test', 'test4'], 'returnFunction');
        \Zaek\Kernel\Hooks::bind(['test', 'test5'], ['StaticTestClass', 'throwTest']);
    }
    public function test1()
    {
        $this->assertEquals(['q'], \Zaek\Kernel\Hooks::trigger('test1', ['q']));
    }
    public function test2()
    {
        $this->assertEquals(['w'], \Zaek\Kernel\Hooks::trigger('test2', ['w']));
    }
    public function test3()
    {
        $this->assertEquals(['w'], \Zaek\Kernel\Hooks::trigger('test3', ['w']));
    }
    public function test4()
    {
        $this->assertEquals(['w'], \Zaek\Kernel\Hooks::trigger('test4', ['w']));
    }
    public function test5()
    {
        $this->expectException(Exception::class);
        $this->assertNull(\Zaek\Kernel\Hooks::trigger('test5'));
    }
}