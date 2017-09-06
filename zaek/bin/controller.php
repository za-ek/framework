<?php
require __DIR__ . '/../lib/zaek/engine/cmain.php';
$app = new class extends \zaek\engine\CMain{
    public function data()
    {
        if ( is_null($this->_data) ) {
            $this->_data = new \zaek\data\mysqli\CConnector($this);
        }
        return parent::data();
    }
};

$app->conf()->addFile(__DIR__ . '/../conf/default.ini.php', 'ini');
$app->conf()->addFile(__DIR__ . '/../conf/mysqli.ini.php', 'ini');
$app->conf()->addFile($_SERVER['DOCUMENT_ROOT'] . '/config.ini.php', 'ini');
$app->conf()->push([
    'request' => [
        'uri' => $_SERVER["REQUEST_URI"] ?? $_SERVER["SCRIPT_NAME"]
    ],
    'template' => [
        'use_template' => true
    ]
]);
try {
    $app->run();
} catch ( \zaek\kernel\CException $e ) {
    $e->explain();
}