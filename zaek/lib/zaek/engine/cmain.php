<?php
namespace zaek\engine;

use zaek\data\CCluster;
use zaek\data\ini\CConnector;
use zaek\kernel\CBuffer;
use zaek\kernel\CConfigList;
use zaek\kernel\CDictionary;
use zaek\kernel\CException;
use zaek\kernel\CFile;
use zaek\user\CUser;

class CMain
{
    protected $_config = [];
    protected $_conf = null;
    protected $_fs = null;
    protected $_template = null;
    protected $_data = null;
    protected $_user = null;
    protected $_dic = null;

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

    /**
     * Объект конфигурации
     * @return CConfigList
     */
    public function conf()
    {
        if ( is_null($this->_conf) ) {
            $this->_conf = new CConfigList();

            $this->_conf->push([
                'fs' => [
                    'root' => $_SERVER["DOCUMENT_ROOT"],
                    'content' => $_SERVER['DOCUMENT_ROOT']. '/content'
                ],
                'content' => [
                    'default' => '%DOCUMENT_ROOT%/content',
                    'rule' => '%DOCUMENT_ROOT%/content',
                ],
                'template' => [
                    'use_template' => false,
                    'template_root' => $_SERVER["DOCUMENT_ROOT"] . '/templates',
                    'relative_path' => '',
                ],
                'request' => [
                    'uri' => @$_SERVER["REQUEST_URI"]
                ],
                'language' => [
                    'default' => 'rus',
                    'list' => [
                        'rus', 'eng'
                    ]
                ]
            ]);
        }

        return $this->_conf;
    }

    /**
     * Объект шаблона
     * @return CBuffer
     */
    public function template()
    {
        if ( is_null($this->_template) ) {
            $this->_template = new CBuffer();
        }
        return $this->_template;
    }

    /**
     * Запуск приложения
     */
    public function run()
    {
        if ( $this->conf()->get('template', 'use_template') ) {
            $this->template()->start();
            $this->includeFile($this->conf()->get('template', 'template_root') . '/' .
                $this->conf()->get('template', 'code') . '/template.php');
            echo $this->template()->end();
        } else {
            $file = $this->route($this->conf()->get('request', 'uri'));
            $this->includeFile($file);
        }
    }

    /**
     * Подключает файл с областью видимости приложения
     *
     * @param $file
     * @throws CException
     */
    public function includeFile($file)
    {
        // TODO: Проверка пути файла
        if ( @file_exists($file) ) {
            include $file;
        } else {
            throw new CException('FILE_NOT_FOUND (CMain::includeFile) ['.$file.']');
        }
    }

    /**
     * Преобразовывает URI в путь к файлу
     *
     * @param $uri
     * @return string
     */
    public function route($uri)
    {
        if(strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        if (substr($uri, -1) == '/') $uri .= 'index.php';

        return $this->fs()->fromUri($uri, $this);
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
     * Объект файловой системы
     * @return CFile
     */
    public function fs()
    {
        if ( is_null($this->_fs) ) {
            $this->_fs = new CFile($this);
        }

        return $this->_fs;
    }

    /**
     * Доступ к данным
     * @return CConnector
     */
    public function data()
    {
        if ( is_null($this->_data) ) {
            $this->_data = new CConnector($this);
        }

        return $this->_data;
    }
    public function user()
    {
        if ( is_null($this->_user) ) {
            $this->_user = new CUser($this);
        }

        return $this->_user;
    }
    public function dic()
    {
        if ( is_null($this->_dic) ) {
            $this->_dic = new CDictionary($this);
        }
        return $this->_dic;
    }
}