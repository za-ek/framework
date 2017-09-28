<?php
require __DIR__ . '/../lib/zaek/engine/cmain.php';

/**
 * Контроллер всего сайта
 */
class CMain extends \zaek\engine\CMain
{
    public function __construct()
    {
        spl_autoload_register([
            $this, 'autoload'
        ]);

        $conf_dir =__DIR__ . '/../conf/';
        if ($fs = opendir($conf_dir)) {
            while (false !== ($file_name = readdir($fs))) {
                if ( substr($file_name, -8) == '.ini.php' ) {
                    $this->conf()->addFile($conf_dir . $file_name, 'ini');
                }
            }
            closedir($fs);
        }

        // URI
        $this->conf()->push([
            'request' => [
                'uri' => $_SERVER["REQUEST_URI"] ?? $_SERVER["SCRIPT_NAME"]
            ]
        ]);

        // Панель управления AdminLTE
        if ( strpos($this->conf()->get('request', 'uri'), '/zaek/admin/') === 0 ) {
            $this->conf()->push([
                'template' => [
                    'code' => 'adminlte',
                    'use_template' => true
                ]
            ]);
        }
    }

    public function data()
    {
        if ( is_null($this->_data) ) {
            $this->_data = new \zaek\data\mysqli\CConnector($this);
        }
        return parent::data();
    }
    public function template()
    {
        if ( is_null($this->_template) ) {
            $this->_template = new \zaek\engine\CTemplate($this);
        }

        return parent::template();
    }
}

/**
 * Выбор контроллера в зависимости от запроса
 */
if ( defined('TESTING') && TESTING === true ) {
    include_once __DIR__ . '/controllers/testing.php';
} else if ( isset($_REQUEST['zAjax']) && $_REQUEST['zAjax'] === "1" ) {
    include_once __DIR__ . '/controllers/ajax.php';
} else {
    include_once __DIR__ . '/controllers/default.php';
}
