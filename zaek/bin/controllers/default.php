<?php
// Пример переопределения коннектора по умолчанию
$app = new class extends CMain {
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
                include $this->fs()->getRootPath().  $path;
                die();
            }
        }

        return parent::route($uri);
    }
};

try {
    $app->run();
} catch ( \zaek\kernel\CException $e ) {
    $e->explain();
}