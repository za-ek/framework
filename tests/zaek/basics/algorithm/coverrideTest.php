<?php
use PHPUnit\Framework\TestCase;

class testObject
{
    protected $_val;
    public function __construct($val)
    {
        $this->_val = $val;
    }
    public function getVal($n)
    {
        return $this->_val * $n;
    }
}
class coverrideTest extends TestCase
{
    protected $_override;
    protected function setUp()
    {
        $override = new \Zaek\basics\Algorithm\COverride();
        $override->push(new testObject(1));
        $override->push(new testObject(2));
        $override->push(new testObject(4));

        $this->_override = $override;
    }
    public function testRollForward()
    {
        $this->assertEquals(12, $this->_override->rollMethod('getVal', [3]));
    }
    public function testRollBackward()
    {
        $this->_override->reverse();
        $this->assertEquals(3, $this->_override->rollMethod('getVal', [3]));
    }
}