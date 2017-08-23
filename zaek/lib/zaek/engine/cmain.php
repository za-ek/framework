<?php
namespace zaek\engine;

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

    }

    public function run()
    {

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