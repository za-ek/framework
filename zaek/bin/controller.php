<?php
require __DIR__ . '/../lib/zaek/engine/cmain.php';

// Пример переопределения коннектора по умолчанию
$app = new class extends \zaek\engine\CMain{
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

    /**
     * Если раздача статики не происходит без обработки сервером, например, с помощью nginx
     *
     * @param $uri
     * @return string
     */
    public function route($uri)
    {
        if ( strpos($uri, '?') ) {
            $path = substr($uri, 0, strpos($uri, '?'));
            $extension = $this->fs()->extension($path);
        } else {
            $path = $uri;
            $extension = $this->fs()->extension($uri);
        }


        if ( $extension ) {
            $aStaticExtensions = [
                'js' => 'application/javascript',
                'css' => 'text/css',
                'woff' => 'application/x-font-woff',
                'woff2' => 'application/x-font-woff2',
                'ttf' => 'application/x-font-ttf',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'jpg' => 'image/jpg',
                'jpeg' => 'image/jpg',
            ];

            if (array_key_exists($extension, $aStaticExtensions)) {
                $this->template()->end();
                header('Content-type: ' . $aStaticExtensions[$extension]);
                include $path;
                die();
            }
        }

        return parent::route($uri);
    }
};

spl_autoload_register([
    $app, 'autoload'
]);

// Добавляем конфиги
$app->conf()->addFile(__DIR__ . '/../conf/default.ini.php', 'ini');
$app->conf()->addFile(__DIR__ . '/../conf/mysqli.ini.php', 'ini');
$app->conf()->addFile($_SERVER['DOCUMENT_ROOT'] . '/config.ini.php', 'ini');

// URI
$app->conf()->push([
    'request' => [
        'uri' => $_SERVER["REQUEST_URI"] ?? $_SERVER["SCRIPT_NAME"]
    ]
]);

// Панель управления AdminLTE
if ( strpos($app->conf()->get('request', 'uri'), '/zaek/admin/') === 0 ) {
    $app->conf()->push([
        'template' => [
            'code' => 'adminlte'
        ]
    ]);
}

// Ajax-запросы к виджетам - без шаблона
if ( isset($_REQUEST['zAjax']) && $_REQUEST['zAjax'] === "1" ) {
    $app->conf()->push([
        'template' => [
            'use_template' => false
        ]
    ]);
} else {
    $app->conf()->push([
        'template' => [
            'use_template' => true
        ]
    ]);
}

try {
    $app->run();
} catch ( \zaek\kernel\CException $e ) {
    $e->explain();
}