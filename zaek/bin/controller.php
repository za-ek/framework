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

        $this->conf()->addFile(__DIR__ . '/../conf/default.ini.php', 'ini');
        $this->conf()->addFile(__DIR__ . '/../conf/mysqli.ini.php', 'ini');
        $this->conf()->addFile($_SERVER['DOCUMENT_ROOT'] . '/config.ini.php', 'ini');

        // URI
        $this->conf()->push([
            'request' => [
                'uri' => $_SERVER["REQUEST_URI"] ?? $_SERVER["SCRIPT_NAME"]
            ]
        ]);
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
if ( isset($_REQUEST['zAjax']) && $_REQUEST['zAjax'] === "1" ) {
    include_once __DIR__ . '/controllers/ajax.php';
} else {
    include_once __DIR__ . '/controllers/default.php';
}
