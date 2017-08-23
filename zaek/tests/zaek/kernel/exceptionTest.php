<?php
use PHPUnit\Framework\TestCase;

class cexceptionTest extends TestCase
{
    public function testEmptyString()
    {
        $e = new \zaek\kernel\CException('',1);
        $this->assertEquals('', $e->getSymCode());
        $this->assertEquals([], $e->getFunctions());
        $this->assertEquals([], $e->getAdd());
        $this->assertEquals([], $e->getArg());
    }
    public function testCodeString()
    {
        $e = new \zaek\kernel\CException('TEST_EXCEPTION',1);
        $this->assertEquals('TEST_EXCEPTION', $e->getSymCode());
        $this->assertEquals([], $e->getFunctions());
        $this->assertEquals([], $e->getAdd());
        $this->assertEquals([], $e->getArg());
    }
    public function testCodeClassString()
    {
        $e = new \zaek\kernel\CException('TEST_EXCEPTION (CException::__construct)',1);
        $this->assertEquals('TEST_EXCEPTION', $e->getSymCode());
        $this->assertEquals([
            [
                'type' => 'class_method',
                'val' => [
                    0 => 'CException',
                    1 => '__construct'
                ]
            ]
        ], $e->getFunctions());
        $this->assertEquals([], $e->getAdd());
        $this->assertEquals([], $e->getArg());
    }
    public function testCodeClassParamString()
    {
        $e = new \zaek\kernel\CException('TEST_EXCEPTION (CException::__construct) [test]',1);
        $this->assertEquals('TEST_EXCEPTION', $e->getSymCode());
        $this->assertEquals([
            [
                'type' => 'class_method',
                'val' => [
                    0 => 'CException',
                    1 => '__construct'
                ]
            ]
        ], $e->getFunctions());
        $this->assertEquals([
            'test'
        ], $e->getAdd());
        $this->assertEquals([], $e->getArg());
    }
    public function testCodeClassParamArgString()
    {
        $e = new \zaek\kernel\CException('TEST_EXCEPTION (CException::__construct) [test] {1}',1);
        $this->assertEquals('TEST_EXCEPTION', $e->getSymCode());
        $this->assertEquals([
            [
                'type' => 'class_method',
                'val' => [
                    0 => 'CException',
                    1 => '__construct'
                ]
            ]
        ], $e->getFunctions());
        $this->assertEquals([
            'test'
        ], $e->getAdd());
        $this->assertEquals([
            1
        ], $e->getArg());
    }
    public function testCodeClass2ParamArgString()
    {
        $e = new \zaek\kernel\CException('TEST_EXCEPTION (CException::__construct) [test, "A"] {1}',1);
        $this->assertEquals('TEST_EXCEPTION', $e->getSymCode());
        $this->assertEquals([
            [
                'type' => 'class_method',
                'val' => [
                    0 => 'CException',
                    1 => '__construct'
                ]
            ]
        ], $e->getFunctions());
        $this->assertEquals([
            'test', '"A"'
        ], $e->getAdd());
        $this->assertEquals([
            1
        ], $e->getArg());
    }
}