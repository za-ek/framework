<?php
use PHPUnit\Framework\TestCase;

class cexceptionTest extends TestCase
{
    public function testEmptyString()
    {
        $e = new \Zaek\Kernel\CException('',1);
        $this->assertEquals('', $e->getSymCode());
        $this->assertEquals([
            [
                'type' => 'class_method',
                'val' => [
                    0 => __CLASS__,
                    1 => __FUNCTION__
                ]
            ]
        ], $e->getFunctions());
        $this->assertEquals([], $e->getAdd());
        $this->assertEquals([], $e->getArg());
    }
    public function testCodeString()
    {
        $e = new \Zaek\Kernel\CException('TEST_EXCEPTION',1);
        $this->assertEquals('TEST_EXCEPTION', $e->getSymCode());
        $this->assertEquals([
            [
                'type' => 'class_method',
                'val' => [
                    0 => __CLASS__,
                    1 => __FUNCTION__
                ]
            ]
        ], $e->getFunctions());
        $this->assertEquals([], $e->getAdd());
        $this->assertEquals([], $e->getArg());
    }
    public function testCodeClassString()
    {
        $e = new \Zaek\Kernel\CException('TEST_EXCEPTION (CException::__construct)',1);
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
        $e = new \Zaek\Kernel\CException('TEST_EXCEPTION (CException::__construct) [test]',1);
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
        $e = new \Zaek\Kernel\CException('TEST_EXCEPTION (CException::__construct) [test] {1}',1);
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
        $e = new \Zaek\Kernel\CException('TEST_EXCEPTION (CException::__construct) [test, "A"] {1}',1);
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