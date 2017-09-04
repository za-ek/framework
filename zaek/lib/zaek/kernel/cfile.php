<?php
namespace zaek\kernel;

use zaek\engine\CMain;

class CFile
{
    // File types
    const TYPE_TEXT  = 0x000;
    const TYPE_XML   = 0x001;
    const TYPE_INI   = 0x010;
    const TYPE_LANG  = 0x011;
    const TYPE_CONF  = 0x100;
    const TYPE_JSON  = 0x101;
    const TYPE_MIX   = 0x111;
    const TYPE_RAW   = 0x1000;
    const TYPE_ARR   = 0x1001;

    // Mode options
    const MODE_R  = 1; // 1
    const MODE_RW = 3; // 11
    const MODE_W  = 2; // 10
    const MODE_WR = 3; // 11
    const MODE_A  = 4; // 100
    const MODE_AR = 5; // 101
    const MODE_X  = 8; // 1000
    const MODE_XR = 9; // 1001
    const MODE_C  = 16;// 10000
    const MODE_CR = 17;// 10001
    const MODE_B  = 32;// 100000
    const MODE_INC= 64;

    // Default rules for new files&directories
    protected static $_defaultDirRules = 0755;
    protected static $_defaultFileRules = 0750;

    // Default "fileinfo" array
    public static $FILE_INFO = array(
        "dev" => null, "ino" => null, "mode" => null, "nlink" => null,
        "uid" => null, "gid" => null, "rdev" => null, "size" => null,
        "atime" => null, "mtime" => null, "ctime" => null,
        "blksize" => null, "blocks" => null
    );

    // Errors
    const E_ACCESS_DENIED    =  4;
    const E_NOT_EXISTS       =  5;
    const E_OPENED           =  6;
    const E_EXISTS           =  7;

    /**
     * @en Framework root directory
     * @es Ruta a directoria del framework
     * @ru Путь к директории фреймворка
     *
     * @var string
     */
    private $_framework_root = false;

    /**
     * @en Site root
     * @es Directorio raíz del sitio
     * @ru Корень файловой системы сайта
     *
     * @var string
     */
    private $_root = false;

    protected $_conf;

    /**
     * CFile constructor.
     * @param CMain $app
     */
    public function __construct(CMain $app)
    {
        $this->_conf = $app->conf();
    }
    /**
     * @en Return path to root directory
     * @es Devuelve la ruta al directorio raíz del sitio
     * @ru Возвращает путь к корню сайта
     *
     * @return string
     */
    public function getRootPath()
    {
        if ( $this->_root === false ) {
            $this->_root = $this->_conf->get('fs', 'root');

            if (substr($this->_root, -1) == '/') {
                $this->_root = substr($this->_root, 0, -1);
            }
        }

        return $this->_root;
    }
    /**
     * @en Return path to framework root directory
     * @es Devuelve la ruta al directorio raíz del framework
     * @ru Возвращает путь к директории фреймворка
     *
     * @return string
     */
    public function getFrameworkRootPath()
    {
        if ( $this->_framework_root === false ) {
            try {
                $this->_framework_root = $this->_conf->get('fs', 'framework_root');
            } catch (CException $e) {
                $this->_framework_root = $this->getRootPath();
            }

            if (substr($this->_framework_root, -1) == '/') {
                $this->_framework_root = substr($this->_framework_root, 0, -1);
            }
        }

        return $this->_framework_root;
    }

    /**
     * Returns file path by replacing constants:
     *
     * %DOCUMENT_ROOT%          Document root path (it`s not the same as $_SERVER["DOCUMENT_ROOT"]) (/)
     * %SYSTEM_ROOT%            Framework root path (/zaek)
     * %TEMPLATE_ROOT%          Template root path (/zaek/tpl)
     * %PAGE_TEMPLATE_ROOT%     Page templates path (/zaek/tpl/pages)
     * %WIDGET_TEMPLATE_ROOT%   Widget templates path (/zaek/tpl/widgets)
     * %CACHE_ROOT%             Cache directory (/zaek/tmp/cache)
     * %LANGUAGE_ROOT%          Language directory (/zaek/local)
     * %WIDGET_ROOT%            Widget directory (/zaek/bin/widgets)
     * %MODULES_ROOT%           Language directory (/zaek/lib)
     * %UPLOAD_ROOT%            Language directory (/zaek/tmp/upload)
     *
     * @param string $file - path with constants
     * @return string
     */
    public function convertPath($file)
    {
        $root = $this->getRootPath();
        $framework_root = $this->getFrameworkRootPath();

        return str_replace(
            array(
                '%DOCUMENT_ROOT%',
                '%SYSTEM_ROOT%',
                '%TEMPLATE_ROOT%',
                '%PAGE_TEMPLATE_ROOT%',
                '%WIDGET_TEMPLATE_ROOT%',

                '%ADMIN_ROOT%',
                '%DATA_ROOT%',
                '%CACHE_ROOT%',
                '%LANGUAGE_ROOT%',
                '%WIDGET_ROOT%',

                '%MODULES_ROOT%',
                '%UPLOAD_ROOT%',

                '%NOTIFICATION_ROOT%',
            ),
            array(
                $root ,
                $framework_root  . '/zaek',
                $framework_root  . '/zaek/tpl',
                $framework_root  . '/zaek/tpl/pages',
                $framework_root  . '/zaek/tpl/widgets',

                $framework_root  . '/zaek/admin',
                $framework_root  . '/zaek/tmp/data',
                $framework_root  . '/zaek/tmp/cache',
                $framework_root  . '/zaek/local',
                $framework_root  . '/zaek/bin/widgets',

                $framework_root  . '/zaek/lib',
                $framework_root  . '/zaek/tmp/upload',

                $framework_root  . '/zaek/bin/notifications',
            ),
            $file
        );
    }


    /**
     * @en Return file content
     * @es Devuelve contenido del fichero
     * @ru Возвращает содержимое файла
     *
     * @param $file
     * @return string
     * @throws CException
     */
    public function getContent ($file)
    {
        if($this->checkRules($file, $this::MODE_R, true)) {
            $content = file_get_contents($this->convertPath($file));
            return $content;
        } else {
            throw new CException("CANNOT_GET_FILE_CONTENT [".$file."]", $this::E_ACCESS_DENIED);
        }
    }

    /**
     * @en Returns resource pointer for file $file with mode $mode
     * throws exception on:
     * 1. File not exists
     * 2. File access denied by external rules
     * 3. File access denied by internal rules
     *
     * @ru Возвращает указатель на открытый поток файла, возможные исключения:
     * 1. Файл не существует
     * 2. Доступ запрещён внешними правами доступа
     * 3. Доступ запрещён правами доступа файловой системы
     *
     * @es Devuelve un recurso de puntero a fichero si tiene éxito o lanza una excepción si:
     * 1. El archivo no existe
     * 2. El sistema de archivos no permite abrir este fichero
     * 3. No se permite abrir este fichero por reglas externas
     *
     * @param string $file - file path
     * @param string $mode - file open mode
     *
     * @return resource|null
     */
    public function getStream ($file, $mode)
    {
        $file = $this->convertPath($file);

        if(strlen($file) == 0) return NULL;

        if($this->checkRules($file, $mode)) {
            return fopen($file, $this->getSMode($mode));
        } else {
            return NULL;
        }
    }

    /**
     * @en Checks file accessibility it returns true by default and should be overrided
     *
     * @es Comprueba accesibilidad para el fichero $file en modo $mode, debe pasar por alto
     *
     * @ru Проверяет доступ к файлу на уровне внешних прав доступа, используется как
     * заглушка - следует переопределить в дочернем классе
     *
     *
     * @param string $file - file path
     * @param int $mode - file access mode
     * @return bool
     */
    protected function checkFileRules ($file, $mode = self::MODE_R)
    {
        return ( ( $this->getAbsolute($file) ) && (
                ($this->_root || strpos($file, $this->_root) === 0) ||
                strpos($file, sys_get_temp_dir()) === 0
            ) );
    }

    public function getAbsolute($path)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    /**
     * @param $f
     * @return mixed
     */
    public function getRelativePath($f)
    {
        if ( !$this->getRootPath() || strpos( $f, $this->getRootPath()) === 0 ) {
            $f = substr($f, strlen($this->getRootPath()));
        }

        return $f;
    }

    /**
     * @en Checks file permissions for user with $uid and $gid in $mode mode
     * Using current user by default for $uid and $gid values
     *
     * @ru Проверяет наличие доступа к файлу на уровне прав доступа файловой системы
     *
     * @param string $file - path to file
     * @param string $mode - required permissions
     * @param null $gid - group id
     * @param null $uid - user id
     * @return bool
     */
    public function checkExtRules ($file, $mode, $gid = null, $uid = null)
    {
        if ( is_null ($gid ) ) {
            $gid = posix_getgid();
        }

        if ( is_null ($uid ) ) {
            $uid = posix_getuid();
        }


        if ( !is_null($gid) && !is_null($uid) ) {
            if ($mode & self::MODE_W && !is_dir($file)) {
                if (file_exists($file)) {
                    $owngid = filegroup($file);
                    $ownuid = fileowner($file);
                    $perms = fileperms($file);

                    // Владелец совпадает с пользователем, от имени которого запущен скрипт
                    if (($ownuid == $uid) && $perms & 0x0080) {
                        return true;
                    }
                    // Группа владельца совпадает с группой пользователя, от имени которого запущен скрипт
                    if (($owngid == $gid) && $perms & 0x0010) {
                        return true;
                    }
                    // Права для всех остальных
                    return ($perms & 0x0002);
                } else {
                    return $this->checkExtRules(dirname($file), $mode);
                }
            } elseif (is_dir($file)) {
                if (file_exists($file)) {
                    $owngid = filegroup($file);
                    $ownuid = fileowner($file);
                    $perms = fileperms($file);

                    if ($ownuid == $uid) {
                        return $perms & 0x0080;
                    } elseif ($owngid == $gid) {
                        return $perms & 0x0010;
                    } else {
                        return $perms & 0x0002;
                    }
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * @en Check external & internal rules for file $file in mode $mode
     * $b - throw an exception if file does not exist
     *
     * @es Comprueba el acceso a un fichero $file en modo $mode
     * tanto por sistema de archivos como por reglas externos,
     * el parametro $b indica necesidad de existencia del fichero
     * (lanza excepción si el fichero no existe)
     *
     * @ru Проверяет внешние права и права файловой системы
     * для доступа к файлу $file в режиме $mode, если флаг $b задан
     * как true - выбросит исключение если файл не существует
     *
     * @param string $file - file path
     * @param string $mode - file access mode
     * @param bool $b - check if file exist
     *
     * @throws CException
     * @return bool
     */
    public function checkRules($file, $mode, $b = false)
    {
        $file = $this->convertPath($file);

        if($b && !file_exists($file)) {
            throw new CException("FILE_NOT_EXISTS [".$file."]", $this::E_NOT_EXISTS);
        }

        return $this->checkFileRules($file, $mode) && $this->checkExtRules($file, $mode);
    }

    /**
     * @en Return file access mode string base on class constants MODE_*
     * @es Devuelve una cadena de modo de acceso a un fichero a partir de las constantes
     * de clase MODE_*
     * @ru Возвращает строку режима доступа к файлу из констант класса MODE_*
     *
     * @param string $mode
     * @return string
     */
    public function getSMode($mode)
    {
        $s = "";

        if($mode & self::MODE_R)
            $s = "r";
        if($mode & self::MODE_A)
            $s = "a";
        if($mode & self::MODE_X)
            $s = "x";
        if($mode & self::MODE_C)
            $s = "c";
        if($mode & self::MODE_W)
            $s = "w";


        if($mode != self::MODE_R && ($mode & self::MODE_R)) {
            $s .= "+";
        }

        if($mode & self::MODE_B)
            $s .= "b";

        return $s;
    }

    /**
     * @param $file
     * @param $content
     * @param bool|false $bAppend
     * @param bool $bMakeIfNE
     * @return bool
     * @throws CException
     */
    public function fillContent($file, $content, $bAppend = false, $bMakeIfNE = false)
    {
        $file = $this->convertPath($file);

        if ( !file_exists($file) ) {
            if ( $bMakeIfNE ) {
                if ( $this->checkRules($file, $this::MODE_W) ) {
                    touch($file);
                } else {
                    throw new CException("CANNOT_CREATE_FILE [".$file."]", $this::E_ACCESS_DENIED);
                }
            } else {
                throw new CException("FILE_NOT_EXISTS [" . $file . "]", $this::E_NOT_EXISTS);
            }
        }

        $fs = $this->getStream($file, ($bAppend) ? $this::MODE_A : $this::MODE_W);

        if($fs !== NULL) {

            fputs($fs, $content);
            fclose($fs);

            return true;
        } else {
            return false;
        }

    }

    /**
     * @en Get script path from request string $uri
     * @es Obtiene el camino a fichero de script a partir de petición $uri
     * @ru Полуает путь к исполняемому файлу из строки запроса uri
     *
     * @param $uri
     * @param CMain $app
     * @return bool|string
     * @throws CException
     */
    public function fromUri($uri, CMain $app)
    {
        if ( !$uri ) {
            return false;
        }

        if(strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        $conf = $app->conf();

        // Пути к директориям
        $aDir = array();
        // %DOCUMENT_ROOT%
        $aDir['root'] = $this->getRootPath();
        // %DOCUMENT_ROOT%/content/default
        $aDir['default'] = $this->convertPath($conf->get('content', 'default'));
        // %DOCUMENT_ROOT%/content/%SITE_NAME%
        $aDir['site'] = $this->convertPath($conf->get('content', 'rule'));

        // Правила обработки адреса
        $aFiles = array_unique(array(
            $aDir['site'] .'/.rewrite.php',
            $aDir['default'] .'/.rewrite.php',
            $aDir['root'] .'/.rewrite.php',
        ));

        $aRules = array();

        $obj = $this;

        array_map(function($el) use (&$aRules, $obj) {
            try {
                if ($obj->checkRules($el, $obj::MODE_R)) {
                    $rules = json_decode($this->getContent($el), 1);
                    if ($rules && is_array($rules)) {
                        $aRules = array_merge($aRules, $rules);
                    }
                }
            } catch ( CException $e ) {
            }
        }, $aFiles);

        // Правила обработки адреса
        $aFiles = array_unique(array(
            $aDir['root'] .'/.ignore.php',
            $aDir['default'] .'/.ignore.php',
            $aDir['site'] .'/.ignore.php',
        ));

        $obj = $this;
        $aIgnore = array(
            'uri' => array()
        );

        array_map(function($el) use (&$aIgnore, $obj) {
            try {
                if ($obj->checkRules($el, $obj::MODE_R)) {
                    if ( $ar = json_decode($this->getContent($el), 1) ) {
                        if ( isset($ar['uri']) ) {
                            $aIgnore['uri'] = array_merge($aIgnore['uri'], $ar['uri']);
                        }
                    }
                }
            } catch ( CException $e ) {
            }
        }, $aFiles);

        // Обработка адреса согласно правилам
        foreach ($aRules as $k => $v ) {
            $uri = @preg_replace($k, $v, $uri);
        }

        $uri = preg_replace('#[/]{2,}#', '/', $uri);

        if (substr($uri, -1) == '/') $uri .= 'index.php';

        $uri = $this->getRelativePath($uri);

        // Путь к подключаемому файлу
        $aFile = array();
        // В корне
        $aFile['root'] = $aDir['root'] . '/' . $uri;
        // По умолчанию
        $aFile['default'] = $aDir['default'] . '/' . $uri;
        // Для текущего сайта
        $aFile['site'] = $aDir['site'] . '/' . $uri;

        $file = false;

        foreach(array('site', 'default', 'root') as $type) {
            while(strpos($aFile[$type], '//') !== false) {
                $aFile[$type] = preg_replace('#[/]{2,}#', '/', $aFile[$type]);
            }

            $q = strpos($aFile[$type], "?");

            try {
                if($this->checkRules(($q) ? substr($aFile[$type], 0, $q) : $aFile[$type] , $this::MODE_R, true)) {
                    $file = $aFile[$type];
                    break;
                }
            } catch ( CException $e ) {
            }
        }

        if ( in_array($uri, $aIgnore['uri'] ) ) {
            throw new CException('IGNORE_URI ['.$file.','.$uri.']', 1);
        }

        return $file;
    }

    public function extension($str)
    {
        if(strrpos($str, ".", strrpos($str, DIRECTORY_SEPARATOR)) !== false) {
            return substr($str, strrpos($str, ".", strrpos($str, DIRECTORY_SEPARATOR)) + 1);
        } else {
            return false;
        }
    }
    public function copyFile($file, $file_dst, $overwrite = false, $make_dirs = true)
    {
        if(!$file) throw new CException("INVALID_SOURCE_FILE", 11);
        if(!$file_dst) throw new CException("INVALID_DESTINATION_FILE", 11);

        if(!$this->checkRules($file, CFile::MODE_R, true)) throw new CException('FILE_ACCESS_DENIED ['.$file.']', 1);
        if(!$this->checkRules($file_dst, CFile::MODE_W)) throw new CException('FILE_ACCESS_DENIED ['.$file_dst.']', 1);


        if(@file_exists($file_dst)) {
            if($overwrite) {
                unlink($file_dst);
            } else {
                throw new CException("FILE_ALREADY_EXISTS [{$file_dst}]", $this::E_EXISTS);
            }
        }

        if(@file_exists(dirname($file_dst)) || ($make_dirs && mkdir(dirname($file_dst), 0777, true))) {
            return copy($file, $file_dst);
        } else {
            return false;
        }
    }
    /**
     * Возвращает структуру файловой системы в необходимом формате
     *
     * @param string $root_path Путь, начиная с которого строить иерархию
     * @param int|string $type Тип возвращаемых данных (CFile::TYPE_ARR | CFile::TYPE_XML | CFile::TYPE_JSON)
     *
     * @param string|callback $filter
     * @return mixed Возвращает структуру файловой системы (по умолчанию в формате CFile::TYPE_ARR)
     */
    public function getFS($root_path, $type = self::TYPE_ARR, $filter = "*")
    {
        if(gettype($filter) != 'object') {
            $filter = '/^'.str_replace('*', '(.*)', $filter).'$/';
        }


        if($type == $this::TYPE_ARR) {
            $files  = array('files'=>array(), 'dirs'=>array());
            $directories  = array();

            $root_path = $this->convertPath($root_path);

            $root_path = realpath($root_path);

            if($root_path) {
                $last_letter  = $root_path[strlen($root_path)-1];
                $root_path  = ($last_letter == '\\' || $last_letter == '/') ? $root_path : $root_path.DIRECTORY_SEPARATOR;
                $root_path = str_replace("\\", "/", $root_path);



                $directories[]  = $root_path;

                while (sizeof($directories)) {
                    $dir  = array_pop($directories);
                    $handle = opendir($dir);
                    if (is_resource($handle)) {
                        while (false !== ($file = readdir($handle))) {
                            if ($file == '.' || $file == '..')
                                continue;

                            $file  = $dir.$file;
                            if (is_dir($file)) {
                                $directory_path = $file.DIRECTORY_SEPARATOR;
                                if(
                                    ((gettype($filter) == 'object') && call_user_func($filter, $directory_path)) ||
                                    (is_string($filter) && preg_match($filter, substr($directory_path, strrpos($directory_path, "/")+1)))
                                ) {

                                    array_push($directories, $directory_path);
                                    $files['dirs'][]  = $directory_path;
                                }
                            } elseif (is_file($file)) {

                                if(
                                    ((gettype($filter) == 'object') && call_user_func($filter, $file)) ||
                                    (is_string($filter) && preg_match($filter, substr($file, strrpos($file, "/")+1)))
                                ) {
                                    $files['files'][]  = $file;
                                }
                            }
                        }
                        closedir($handle);
                    }
                }
            }

            return $files;
        }
    }
    public function getFileList($path, $bAbs = true)
    {
        $aResult = array(
            'files' => array(),
            'dirs' => array(),
        );
        $root = $this->convertPath($path);
        $aList = scandir($root);

        foreach ( $aList as $k => $v ) {
            if ( $v != '.' && $v != '..' ) {
                $path = $root . '/' . $v;
                $aResult[ (is_dir($path)) ? 'dirs' : 'files' ][] = ($bAbs) ? $root . '/' . $v : $v;
            }
        }

        return $aResult;
    }
    /**
     * Создаёт файл и наполняет содержимым, в случае необходимости
     *
     * @param string $file Путь к создаваемому файлу
     * @param string $content Содержание файла, по умолчанию - NULL
     * @param int $type Предустановленный тип заполнения файла, по умолчанию - текст
     * @param bool $rules Права на файл
     * @return bool
     * @throws CException
     */
    public function makeFile($file, $content = NULL, $type = CFile::TYPE_TEXT, $rules = false)
    {
        $file = self::convertPath($file);
        if(file_exists($file)) {
            throw new CException("FILE_ALREADY_EXISTS [$file]", $this::E_EXISTS);
        }

        if($this->checkFileRules($file, $this::MODE_W)) {
            if(@touch($file, time())) {
                if($rules !== false) {
                    chmod($file, $rules);
                } else {
                    chmod($file, self::$_defaultFileRules);
                }

                return $this->fillContent($file, $content, $type);
            } else {
                throw new CException("FILE_ACCESS_DENIED", $this::E_ACCESS_DENIED);
            }
        } else {
            throw new CException("FILE_ACCESS_DENIED", $this::E_ACCESS_DENIED);
        }
    }
}