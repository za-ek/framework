<?php
// Пример переопределения коннектора по умолчанию
$app = new class extends CMain {
    public function includeFile($file, $bRepeat = true)
    {
        return parent::includeFile($file, false);
    }
};

try {
    $app->run();
} catch ( \zaek\kernel\CException $e ) {
    $e->explain();
}