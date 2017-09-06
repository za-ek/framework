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
        \zaek\kernel\CHooks::bind(['test', 'test1'], function($param) {return $param;});
        \zaek\kernel\CHooks::bind(['test', 'test2'], [new class {public function a($param){return $param;}}, 'a']);
        \zaek\kernel\CHooks::bind(['test', 'test3'], ['StaticTestClass', 'test']);
        \zaek\kernel\CHooks::bind(['test', 'test4'], 'returnFunction');
        \zaek\kernel\CHooks::bind(['test', 'test5'], ['StaticTestClass', 'throwTest']);
    }
    public function test1()
    {
        $this->assertEquals(['q'], \zaek\kernel\CHooks::trigger('test1', ['q']));
    }
    public function test2()
    {
        $this->assertEquals(['w'], \zaek\kernel\CHooks::trigger('test2', ['w']));
    }
    public function test3()
    {
        $this->assertEquals(['w'], \zaek\kernel\CHooks::trigger('test3', ['w']));
    }
    public function test4()
    {
        $this->assertEquals(['w'], \zaek\kernel\CHooks::trigger('test4', ['w']));
    }
    public function test5()
    {
        $this->expectException(Exception::class);
        $this->assertNull(\zaek\kernel\CHooks::trigger('test5'));
    }
}