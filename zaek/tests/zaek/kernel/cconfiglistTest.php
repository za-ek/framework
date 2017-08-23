<?php
use PHPUnit\Framework\TestCase;

class cconfiglistTest extends TestCase
{
    public function testDemand()
    {
        $list = new \zaek\kernel\CConfigList();
        $list->addFile(__DIR__ . '/../../../conf/default.ini.php', 'ini');

        $list->push(new \zaek\kernel\CConfig([
            'template' => [
                'path' => '%DOCUMENT_ROOT%/templates'
            ]
        ]));

        $this->assertEquals('%DOCUMENT_ROOT%/templates', $list->get('template','path'));
        $this->assertEquals(0, $list->offsetGet(1)[2]);

        $this->assertEquals('default', $list->get('template','code'));

    }
    public function testCorrectOrder()
    {
        $list = new \zaek\kernel\CConfigList();
        $list->addFile(__DIR__ . '/../../../conf/default.ini.php', 'ini');

        $list->push(new \zaek\kernel\CConfig([
            'template' => [
                'path' => '%DOCUMENT_ROOT%/templates',
                'use_buffer' => false
            ]
        ]));

        $this->assertEquals('%DOCUMENT_ROOT%/templates', $list->get('template','path'));
        $this->assertEquals(0, $list->offsetGet(1)[2]);

        $this->assertEquals('default', $list->get('template','code'));
        $this->assertEquals(false, $list->get('template','use_buffer'));

    }
}