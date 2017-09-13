<?php
use PHPUnit\Framework\TestCase;

class templateTest extends TestCase
{
    /**
     * @var \zaek\engine\CMain
     */
    protected $_app;

    protected function setUp()
    {
        $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../../../');
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['SERVER_NAME'] = 'localhost';

        $this->_app = new \zaek\engine\CMain();
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
    public function testCss()
    {
        $this->_app->template()->addCss('\style.css');
        $this->assertEquals([[
                'http://localhost/templates/none/css/style.css',
                ''
            ]], $this->_app->template()->getCss()
        );

        $this->_app->template()->clearProp('css');

        $this->_app->template()->addCss('http://example.com/style.css');
        $this->assertEquals([[
                'http://example.com/style.css',
                ''
            ]], $this->_app->template()->getCss()
        );

        $this->_app->template()->clearProp('css');

        $this->_app->template()->addCss('//example.com/style.css');
        $this->assertEquals([[
                'http://example.com/style.css',
                ''
            ]], $this->_app->template()->getCss()
        );
        $this->_app->template()->clearProp('css');

        $this->_app->template()->addCss('/style.css');
        $this->assertEquals([[
                'http://localhost/style.css',
                ''
            ]], $this->_app->template()->getCss()
        );
    }
    public function testJs()
    {
        $this->_app->template()->addJs('\main.js');
        $this->assertEquals([[
            'http://localhost/templates/none/js/main.js',
            ''
        ]], $this->_app->template()->getJs()
        );

        $this->_app->template()->clearProp('js');

        $this->_app->template()->addJs('http://example.com/main.js');
        $this->assertEquals([[
            'http://example.com/main.js',
            ''
        ]], $this->_app->template()->getJs()
        );

        $this->_app->template()->clearProp('js');

        $this->_app->template()->addJs('//example.com/main.js');
        $this->assertEquals([[
            'http://example.com/main.js',
            ''
        ]], $this->_app->template()->getJs()
        );
        $this->_app->template()->clearProp('js');

        $this->_app->template()->addJs('/main.js');
        $this->assertEquals([[
            'http://localhost/main.js',
            ''
        ]], $this->_app->template()->getJs()
        );
    }
    public function testImage()
    {
        $this->assertEquals(
            'http://localhost/templates/none/images/test.png',
            $this->_app->template()->img('test.png')
        );
    }
}