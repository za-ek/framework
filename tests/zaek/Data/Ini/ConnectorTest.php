<?php
use PHPUnit\Framework\TestCase;

class ConnectorTest extends TestCase
{
    /**
     * @var \Zaek\Engine\Main
     */
    protected $_app;

    protected function setUp()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../../../');
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['SERVER_NAME'] = 'localhost';

        $this->_app = new class extends \Zaek\Engine\Main {
            public function fs()
            {
                if ( is_null($this->_fs) ) {
                    $this->_fs = new class($this) extends \Zaek\Kernel\File {
                        public function convertPath($file)
                        {
                            return parent::convertPath(
                                str_replace('%DATA_ROOT%', $this->getRootPath() . '/tmp/data/', $file)
                            );
                        }
                    };
                }

                return parent::fs();
            }
        };
        $this->_app->conf()->push([
            'fs' => [
                'root' => $_SERVER['DOCUMENT_ROOT'],
                'framework_root' => $_SERVER['DOCUMENT_ROOT'] . '/'
            ],
            'request' => [
                'uri' => '/test_dir/'
            ],
            'content' => [
                'default' => '%DOCUMENT_ROOT%/content',
                'rule' => '%DOCUMENT_ROOT%/content',
            ],
            'template' => [
                'code' => 'none',
                'template_root' => $_SERVER['DOCUMENT_ROOT'] . '/templates'
            ]
        ]);

    }
    public function testInsert()
    {
        $this->_app->data()->insert('users', [
            'login' => 'za-ek',
        ]);
    }
}