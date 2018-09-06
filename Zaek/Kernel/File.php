<?php
namespace Zaek\Kernel;

use Zaek\Engine\Main;
use Zaek\Kernel\Exception\FileNotExists;

class File
{
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

    /**
     * Project root directory
     *
     * @var string
     */
    private $_root = false;

    protected $_conf;

    private $_map;

    /**
     * File constructor.
     * @param Main $app
     */
    public function __construct(Main $app)
    {
        $this->_conf = $app->conf();

        $root = $this->getRootPath();

        $this->_map = [
            [
                '%DOCUMENT_ROOT%',
            ],
            [
                $root ,
            ]
        ];
    }

    /**
     * Add path conversion (%DOCUMENT_ROOT% => '/')
     *
     * @param $code
     * @param $path
     */
    public function registerPath($code, $path)
    {
        if(in_array($code, $this->_map[0])) {
            throw new \DomainException("Code already exist <{$code}>");
        }

        $this->_map[0][] = $code;
        $this->_map[1][] = $path;
    }

    /**
     * Return project root directory
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
     * Returns file path by replacing constants:
     *
     * %DOCUMENT_ROOT%          Document root path (it`s not the same as $_SERVER["DOCUMENT_ROOT"]) (/)
     *
     * @param string $file - path with constants
     * @return string
     */
    public function convert($path)
    {
        return str_replace(
            $this->_map[0],
            $this->_map[1],
            $path
        );
    }


    /**
     * Return file content
     *
     * @param $file
     * @return string
     */
    public function getContent ($file_path)
    {
        if($this->checkRules($file_path, $this::MODE_R, true)) {
            $content = file_get_contents($this->convert($file_path));
            return $content;
        } else {
            return '';
        }
    }

    /**
     * Return resource pointer for file $file with mode $mode
     * throws exception on:
     * 1. File not exists
     * 2. File access denied by external rules
     * 3. File access denied by internal rules
     *
     * @param string $file_path - file path
     * @param string $mode - file open mode
     *
     * @return resource|null
     */
    public function getStream ($file_path, $mode)
    {
        $file_path = $this->convert($file_path);

        if(strlen($file_path) == 0) return NULL;

        if($this->checkRules($file_path, $mode)) {
            return fopen($file_path, $this->getSMode($mode));
        } else {
            return NULL;
        }
    }

    /**
     * Checks file accessibility it returns true by default and should be overrided
     *
     * @param string $file - file path
     * @param int $mode - file access mode
     * @return bool
     */
    protected function checkFileRules ($file, $mode = self::MODE_R)
    {
        return ( ( $this->getAbsolute($file) ) && (
                (!$this->getRootPath() || strpos($file, $this->getRootPath()) === 0) ||
                strpos($file, sys_get_temp_dir()) === 0
            ) );
    }

    /**
     * Return absolute path of given file path (replace "..", "//")
     *
     * @param $path
     * @return string
     */
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
     * Return relative path (web path) for given file path
     *
     * @param $file_path
     * @return mixed
     */
    public function getWebPath($file_path)
    {
        if ( !$this->getRootPath() || strpos( $file_path, $this->getRootPath()) === 0 ) {
            $file_path = substr($file_path, strlen($this->getRootPath()));
        }

        return $file_path;
    }

    /**
     * Checks file permissions for user with $uid and $gid in $mode mode
     * Using current user by default for $uid and $gid values
     *
     * @param string $file - path to file
     * @param string $mode - required permissions
     * @param null $gid - group id
     * @param null $uid - user id
     * @return bool
     */
    public function checkExtRules ($file, $mode, $gid = null, $uid = null)
    {
        if(!extension_loaded('posix')) {
            return true;
        }

        if (is_null($gid)) {
            $gid = posix_getgid();
        }

        if (is_null($uid)) {
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
     * Check external & internal rules for file $file in mode $mode
     * $b - throw an exception if file does not exist
     *
     * @param string $file_path - file path
     * @param string $mode - file access mode
     * @param bool $b - check if file exist
     *
     * @return bool
     */
    public function checkRules($file_path, $mode, $b = false)
    {
        $file_path = $this->convert($file_path);

        if($b && !file_exists($file_path)) {
            throw FileNotExists::create($file_path);
        }

        return $this->checkFileRules($file_path, $mode) && $this->checkExtRules($file_path, $mode);
    }

    /**
     * Return file access mode string base on class constants MODE_*
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
     * Return file extension of given path
     *
     * @param $file_path
     * @return bool|string
     */
    public function extension($file_path)
    {
        if(strrpos($file_path, ".", strrpos($file_path, DIRECTORY_SEPARATOR)) !== false) {
            return substr($file_path, strrpos($file_path, ".", strrpos($file_path, DIRECTORY_SEPARATOR)) + 1);
        } else {
            return false;
        }
    }

    /**
     * Return list of files and directories on a given path
     *
     * @param string $root_path
     * @param string|callback $filter
     * @return array
     */
    public function getFS($root_path, $filter = "*")
    {
        if(gettype($filter) != 'object') {
            $filter = '/^'.str_replace('*', '(.*)', $filter).'$/';
        }


        $files  = array('files'=>array(), 'dirs'=>array());
        $directories  = array();

        $root_path = $this->convert($root_path);

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

            return $files;
        }
    }

    /**
     * @param $path
     * @param bool $bAbs
     * @return array
     */
    public function getFileList($path, $bAbs = true)
    {
        $aResult = array(
            'files' => array(),
            'dirs' => array(),
        );
        $root = $this->convert($path);
        $aList = scandir($root);

        foreach ( $aList as $k => $v ) {
            if ( $v != '.' && $v != '..' ) {
                $path = $root . '/' . $v;
                $aResult[ (is_dir($path)) ? 'dirs' : 'files' ][] = ($bAbs) ? $root . '/' . $v : $v;
            }
        }

        return $aResult;
    }
}