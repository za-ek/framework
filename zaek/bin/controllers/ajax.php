<?php
$app = new CMain;

$app->conf()->push([
    'template' => [
        'use_template' => true,
        'code' => 'empty'
    ]
]);
if ( isset($_REQUEST['zAjaxType']) && $_REQUEST['zAjaxType'] == 'json' ) {
    try {
        echo json_encode([
            'result' => $app->run(false),
            // Сообщение об ошибке
            'error' => false,
            // Код ошибки
            'error_code' => 0,
            // Инфо об ошибке
            'error_params' => array(),
            'page_param' => array(
                'js' => $app->template()->getJs(),
                'css' => $app->template()->getCss(),
                'title' => $app->template()->getTitle(),
            ),
            'params_origin' => (isset($_POST['params']) ? $_POST['params'] : array()),
            'widget_id' => isset($_REQUEST['widget_id']) ? $_REQUEST['widget_id'] : '',
        ]);
    } catch ( \zaek\kernel\CException $e ) {
        echo json_encode([
            'result' => null,
            // Сообщение об ошибке
            'error' => $e->getMessage(),
            // Код ошибки
            'error_code' => $e->getCode(),
            // Инфо об ошибке
            'error_params' => $e->getAdd(),
            'params_origin' => (isset($_POST['params']) ? $_POST['params'] : array()),
            'widget_id' => isset($_REQUEST['widget_id']) ? $_REQUEST['widget_id'] : '',
        ]);
    }
} else {
    try {
        $result = $app->run();
    } catch ( \zaek\kernel\CException $e ) {
        $result = $e->getMessage();
    }
}