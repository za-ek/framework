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

            $aFiles = [];

            for( $i = 0; $i < $za->numFiles; $i++ ){
                $stat = $za->statIndex( $i );
                $aFiles[] = $stat['name'];
            }

            return $aFiles;
        }
    }
}

