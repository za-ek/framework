<?php
namespace zaek\engine;

use zaek\kernel\CBuffer;
use zaek\kernel\CConfigList;
use zaek\kernel\CException;
use zaek\kernel\CFile;

class CMain
{
    protected $_config = [];
    protected $_conf = null;
    protected $_fs = null;
    protected $_template = null;

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
                    'use_template' => false,
                    'template_root' => $_SERVER["DOCUMENT_ROOT"] . '/templates',
                    'relative_path' => '',
                ],
                'request' => [
                    'uri' => @$_SERVER["REQUEST_URI"]
                ]
            ]);
        }

        return $this->_conf;
    }

    public function template()
    {
        if ( is_null($this->_template) ) {
            $this->_template = new CBuffer();
        }
        return $this->_template;
    }

    public function run()
    {
        if ( $this->conf()->get('template', 'use_template') ) {
            $this->template()->start();
            $this->includeFile($this->conf()->get('template', 'template_root') . '/' .
                $this->conf()->get('template', 'code') . '/template.php');
            echo $this->template()->end();
        } else {
            $file = $this->pathFromUri($this->conf()->get('request', 'uri'));
            $this->includeFile($file);
        }
    }

    public function includeFile($file)
    {
        // TODO: Проверка пути файла
        if ( @file_exists($file) ) {
            include $file;
        } else {
            throw new CException('FILE_NOT_FOUND (CMain::includeFile) ['.$file.']');
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

    /**
     * @return null|CFile
     */
    public function fs()
    {
        if ( is_null($this->_fs) ) {
            $this->_fs = new CFile($this);
        }

        return $this->_fs;
    }
}