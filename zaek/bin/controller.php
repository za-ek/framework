<?php
require __DIR__ . '/../lib/zaek/engine/cmain.php';
$app = new \zaek\engine\CMain();

$app->conf()->addFile(__DIR__ . '/../conf/default.ini.php', 'ini');
$app->conf()->push([
    'request' => [
        'uri' => $_SERVER["REQUEST_URI"] ?? $_SERVER["SCRIPT_NAME"]
    ]
]);

$app->run();