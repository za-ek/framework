<?php
use PHPUnit\Framework\TestCase;

class bufferTest extends TestCase
{
    public function testNested()
    {
        $buffer = new \zaek\kernel\CBuffer();
        $buffer_nested = new \zaek\kernel\CBuffer();

        $buffer->start();
        $buffer_nested->start();
        try {
            $buffer->end();
            $this->assertTrue(true, false);
        } catch ( \zaek\kernel\CException $e ) {
            $this->assertTrue(true, true);
        }

        $buffer_nested->end();
        $buffer->end();
    }
}