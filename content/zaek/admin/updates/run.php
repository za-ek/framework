<?php
/**
 * @var $this \zaek\engine\CMain
 */

function recurse_copy($src,$dst) {
    $dir = opendir($src);
    if ( $dir ) {
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}

if ( isset($_REQUEST['params']) && isset($_REQUEST['params']['source']) && isset($_REQUEST['params']['module']) ) {
    switch ($_REQUEST['params']['source']) {
        case 'github':
            $url = 'https://github.com/'.$_REQUEST['params']['module'].'/archive/master.zip';
            $zip_path = __DIR__ . '/temp.zip';
            copy($url, $zip_path);

            $zip = new ZipArchive;
            $zip->open($zip_path);
            $zip->extractTo($this->fs()->convertPath('%UPLOAD_ROOT%'));

            $archive_dir = $this->fs()->convertPath('%UPLOAD_ROOT%/' . explode('/', $_REQUEST['params']['module'])[1] . '-master');

            recurse_copy($archive_dir, $_SERVER['DOCUMENT_ROOT']);

            function rrmdir($dir) {
                if (is_dir($dir)) {
                    $objects = scandir($dir);
                    foreach ($objects as $object) {
                        if ($object != "." && $object != "..") {
                            if (is_dir($dir."/".$object))
                                rrmdir($dir."/".$object);
                            else
                                unlink($dir."/".$object);
                        }
                    }
                    rmdir($dir);
                }
            }

            rrmdir($archive_dir);

            unlink($zip_path);

            break;
        default:
            throw new \zaek\kernel\CException('UNKNOWN_SOURCE_TYPE');
            break;
    }
} else {
    throw new \zaek\kernel\CException('MISSING_PARAM');
}
