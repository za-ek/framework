<?php
use PHPUnit\Framework\TestCase;

class cconfiglistTest extends TestCase
{
    public function testDemand()
    {
        $list = new \Zaek\Kernel\ConfigList(new \Zaek\Engine\Main());
        $list->addFile(__DIR__ . '/../../../conf/default.ini.php', 'ini');

        $list->push(new \Zaek\Kernel\Config([
            'template' => [
                'path' => '%DOCUMENT_ROOT%/templates'
            ]
        ]));

        $this->assertEquals('%DOCUMENT_ROOT%/templates', $list->get('template','path'));
        $this->assertEquals(null, $list[0][2]);

    }
    public function testCorrectOrder()
    {
        $list = new \Zaek\Kernel\ConfigList(new \Zaek\Engine\Main());
        $list->addFile(__DIR__ . '/../../../conf/default.ini.php', 'ini');

        $list->push(new \Zaek\Kernel\Config([
            'template' => [
                'path' => '%DOCUMENT_ROOT%/templates',
                'use_buffer' => false
            ]
        ]));

        $this->assertEquals('%DOCUMENT_ROOT%/templates', $list->get('template','path'));
        $this->assertEquals(null, $list[0][2]);

        $this->assertEquals(false, $list->get('template','use_buffer'));

    }
    public function testInsert()
    {
        $list = new \Zaek\Kernel\ConfigList(new \Zaek\Engine\Main());
        $list->addFile(__DIR__ . '/../../../conf/default.ini.php', 'ini');

        $list->push(new \Zaek\Kernel\Config([
            'template' => [
                'path' => '%DOCUMENT_ROOT%/templates',
                'use_buffer' => false
            ]
        ]));

        $this->assertEquals('%DOCUMENT_ROOT%/templates', $list->get('template','path'));
        $this->assertEquals(null, $list[0][2]);

        $this->assertEquals(false, $list->get('template','use_buffer'));

        $list->push([
            'template' => [
                'use_buffer' => true
            ]
        ]);
        $this->assertEquals(true, $list->get('template','use_buffer'));
        $list->push([
            'user' => [
                'id' => 1
            ]
        ]);
        $this->assertEquals(1, $list->get('user','id'));
    }
    public function issetTest()
    {
        $list = new \Zaek\Kernel\ConfigList(new \Zaek\Engine\Main());

        $list->push([
            'template' => [
                'root_param' => false,
                'leaf_param' => true,
            ]
        ]);
        $list->push(new \Zaek\Kernel\CConfig([
            'template' => [
                'leaf_param' => false
            ]
        ]));

        $this->assertEquals(true, $list->isDefined('template', 'root_param'));
        $this->assertEquals(false, $list->isDefined('template', 'non_exist_param'));
    }
}