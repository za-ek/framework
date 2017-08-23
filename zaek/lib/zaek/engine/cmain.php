<?php
namespace zaek\engine;

use zaek\kernel\CConfigList;

class CMain
{
    protected $_config = [];
    protected $_conf = null;

    public function __construct()
    {
        /**
         * Создаём список конфигов
         * 1. Из массива
         * 2. Конфиг сайта
         * 3. Конфиг проекта
         * 4. Дефолтный конфиг
         */
        spl_autoload_register([
            $this, 'autoload'
        ]);
    }

    public function conf()
    {
        if ( is_null($this->_conf) ) {
            $this->_conf = new CConfigList();

            $this->_conf->push([
                'fs' => [
                    'root' => $_SERVER["DOCUMENT_ROOT"],
                    'content' => $_SERVER['DOCUMENT_ROOT']. '/content'
                ],
                'template' => [
                    'use_template' => false
                ],
                'request' => [
                    'uri' => @$_SERVER["REQUEST_URI"]
                ]
            ]);
        }

        return $this->_conf;
    }

    public function run()
    {
        if ( $this->conf()->get('template', 'use_template') ) {

        } else {
            $file = $this->pathFromUri($this->conf()->get('request', 'uri'));
            if ( @file_exists($file) ) {
                include $file;
            }
        }
    }

    public function pathFromUri($uri)
    {
        if(strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        if (substr($uri, -1) == '/') $uri .= 'index.php';

        return $this->conf()->get('fs','content') . $uri;
    }
    /**
     * Basic autoload
     * @param $class
     */
    public function autoload($class)
    {
        @include __DIR__ . '/../../'. str_replace('\\','/',strtolower($class)). '.php';
    }
}