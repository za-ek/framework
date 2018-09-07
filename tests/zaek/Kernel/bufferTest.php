<?php
use PHPUnit\Framework\TestCase;

class bufferTest extends TestCase
{
    public function testNested()
    {
        $buffer = new \Zaek\Kernel\Buffer();
        $buffer_nested = new \Zaek\Kernel\Buffer();

        $buffer->start();
        $buffer_nested->start();

        try {
            $buffer->end();
            $this->assertTrue(true, false);
        } catch ( \Zaek\Kernel\Exception\IncorrectBufferOrder $e ) {
            $this->assertTrue(true, true);
        }

        $buffer_nested->end();
        $buffer->end();
    }
}