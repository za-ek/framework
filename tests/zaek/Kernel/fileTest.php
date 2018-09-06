<?php
use PHPUnit\Framework\TestCase;

function arrays_are_similar($a, $b) {
    // we know that the indexes, but maybe not values, match.
    // compare the values between the two arrays
    foreach($a as $k => $v) {
        if ( is_array($v) ) {
            return arrays_are_similar($v, $b[$k]);
        } else if ($v !== $b[$k]) {
            return false;
        }
    }
    // we have identical indexes, and no unequal values
    return true;
}

class FileTest extends \Zaek\Kernel\File
{
    public function convertPath($file)
    {
        return parent::convertPath(
            str_replace(
                '%UPLOAD_ROOT%',
                '/tmp',
                $file
            )
        );
    }
    protected function checkFileRules($file, $mode = self::MODE_R)
    {
        return false;
    }
}
class AppTest extends \Zaek\Engine\Main
{
    public function fs()
    {
        if ( is_null($this->_fs) ) {
            $this->_fs = new FileTest($this);
        }

        return parent::fs();
    }
}

class cfileTest extends TestCase
{
    /**
     * @var \Zaek\Engine\Main
     */
    protected $_app;
    /**
     * @var \Zaek\Engine\Main
     */
    protected $_app_local;

    protected function setUp()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../../../');

        $this->_app = new \Zaek\Engine\Main();
        $this->_app->conf()->push([
            'fs' => [
                'root' => $_SERVER['DOCUMENT_ROOT'] . '/zaek/tests/',
                'framework_root' => $_SERVER['DOCUMENT_ROOT'] . '/'
            ],
            'request' => [
                'uri' => '/test_dir/'
            ],
            'content' => [
                'default' => '%DOCUMENT_ROOT%/content',
                'rule' => '%DOCUMENT_ROOT%/content',
            ]
        ]);


        $this->_app_local = new AppTest();
        $this->_app_local->conf()->push([
            'fs' => [
                'root' => $_SERVER['DOCUMENT_ROOT'] . '/zaek/tests/',
                'framework_root' => $_SERVER['DOCUMENT_ROOT'] . '/'
            ],
            'request' => [
                'uri' => '/test_dir/'
            ]
        ]);
    }
    protected function fs()
    {
        return $this->_app->fs();
    }
    /** Соответствие директории фреймворка */
    public function testGetFrameworkRootPath()
    {
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'], $this->fs()->getFrameworkRootPath());
    }
    /** Соответствие корневой директории */
    public function testGetRootPath()
    {
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/tests', $this->fs()->getRootPath());
    }
    /** Соответствие обработки путей к директориям */
    public function testConvertPath()
    {
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/tests', $this->fs()->convertPath('%DOCUMENT_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek', $this->fs()->convertPath('%SYSTEM_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/tpl', $this->fs()->convertPath('%TEMPLATE_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/tpl/pages', $this->fs()->convertPath('%PAGE_TEMPLATE_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/tpl/widgets', $this->fs()->convertPath('%WIDGET_TEMPLATE_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/content/zaek/admin', $this->fs()->convertPath('%ADMIN_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/tmp/cache', $this->fs()->convertPath('%CACHE_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/local', $this->fs()->convertPath('%LANGUAGE_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/bin/widgets', $this->fs()->convertPath('%WIDGET_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/lib', $this->fs()->convertPath('%MODULES_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/tmp/upload', $this->fs()->convertPath('%UPLOAD_ROOT%'));
        $this->assertEquals($_SERVER['DOCUMENT_ROOT'] . '/zaek/bin/notifications', $this->fs()->convertPath('%NOTIFICATION_ROOT%'));
    }
    /** Переопределение метода обработки пути */
    public function testConvertPathOverride()
    {
        $this->assertEquals('/tmp', $this->_app_local->fs()->convertPath('%UPLOAD_ROOT%'));
    }
    public function testGetContent()
    {
        $this->assertEquals('Test content', $this->fs()->getContent('%DOCUMENT_ROOT%/test_dir/1/tmp'));
    }
    public function testGetStream()
    {
        $this->assertEquals(true, is_resource($this->fs()->getStream('%DOCUMENT_ROOT%/test_dir/1/tmp', $this->fs()::MODE_R)));
    }

    public function testExtension()
    {
        $this->assertEquals('php', $this->fs()->extension('/index.php'));
        $this->assertEquals('jpeg', $this->fs()->extension('/index.tmp.jpeg'));
        $this->assertEquals('', $this->fs()->extension('/index'));
        $this->assertEquals('', $this->fs()->extension('/index.php/index'));
        $this->assertEquals('a', $this->fs()->extension('/index.php/index.a'));
    }
    public function testGetFs()
    {
        $root = $_SERVER["DOCUMENT_ROOT"] . '/zaek/tests/test_dir';
        $this->assertTrue(arrays_are_similar([
            'dirs' => [
                $root . '/1/',
                $root . '/2/'
            ],
            'files' => [
                $root . '/2.php',
                $root . '/2/index.php',
                $root . '/index.php',
                $root . '/1/tmp',
                $root . '/1/2',
            ]
        ], $this->fs()->getFS('%DOCUMENT_ROOT%/test_dir/')));

        $this->assertTrue(arrays_are_similar([
            'dirs' => [
            ],
            'files' => [
                $root . '/2.php',
                $root . '/index.php',
            ]
        ], $this->fs()->getFS('%DOCUMENT_ROOT%/test_dir/', $this->_app->fs()::TYPE_ARR, '*.php')));

        $this->assertTrue(arrays_are_similar([
            'dirs' => [
                $root . '/2/'
            ],
            'files' => [
                $root . '/2.php',
                $root . '/2/index.php',
            ]
        ], $this->fs()->getFS('%DOCUMENT_ROOT%/test_dir/', $this->_app->fs()::TYPE_ARR, function($path) {
            return strstr($path, '2');
        })));
    }
}