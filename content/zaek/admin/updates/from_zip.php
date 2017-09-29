<?php
if ( isset($_POST['params']) ) {
    if ( isset($_POST['params']['url']) ) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_POST['params']['url']);
        curl_setopt($ch,CURLOPT_USERAGENT,'zaek-update');
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $file = tempnam(sys_get_temp_dir(), 'zzu');
        $fp = fopen($file, 'w');
        if ( $fp ) {
            fwrite($fp, $output);
            fclose($fp);

            $za = new ZipArchive();
            $za->open($file);

            for( $i = 0; $i < $za->numFiles; $i++ ){
                $stat = $za->statIndex( $i );
                $name = substr($stat['name'], strpos($stat['name'], '/', 1));

                $path = $this->fs()->convertPath('%DOCUMENT_ROOT%' . $name);
                if ( substr($name, -1) == '/' ) {
                    // Директория
                    if ( !file_exists($path) ) {
                        mkdir($path, $this->conf()->get('fs', 'mkdir_rules'));
                    }
                } else {
                    // Файл
                    if ( !file_exists($path) ) {
                        touch($path);
                    }
                    file_put_contents($path, $za->getFromIndex($i));
                }
            }
        }
    }
}

