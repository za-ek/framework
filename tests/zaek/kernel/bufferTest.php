<?php
use PHPUnit\Framework\TestCase;

class bufferTest extends TestCase
{
    public function testNested()
    {
        $buffer = new \Zaek\Kernel\CBuffer();
        $buffer_nested = new \Zaek\Kernel\CBuffer();

        $buffer->start();
        $buffer_nested->start();
        try {
            $buffer->end();
            $this->assertTrue(true, false);
        } catch ( \Zaek\Kernel\CException $e ) {
            $this->assertTrue(true, true);
        }

        $buffer_nested->end();
        $buffer->end();
    }
}