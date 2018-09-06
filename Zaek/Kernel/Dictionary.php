<?php
namespace Zaek\Kernel;

use Zaek\Engine\Main;

class Dictionary
{
    /**
     * @var array
     */
    protected $_content = array();

    /**
     * @var Main
     */
    protected $_app;

    /**
     * Dictionary constructor.
     *
     * @param Main $app
     */
    public function __construct(Main $app)
    {
        $this->_app = $app;
    }

    /**
     * Add translations from a ini file located at $path
     *
     * @param $path
     */
    public function addFile($path)
    {
        $path = str_replace('%LANGUAGE%', $this->getLang(), $this->_app->fs()->convertPath($path));

        if ( $this->_app->fs()->checkRules($path, File::MODE_R) ) {
            $content = (array)@parse_ini_file($path, true);
            if ( array_key_exists($this->getLang(), $content) && is_array($content[$this->getLang()]) ) {
                $this->_content = array_merge($this->_content, $content[$this->getLang()]);
            } else {
                $this->_content = array_merge($this->_content, (array)$content);
            }
        }
    }

    /**
     * Receive one or more parameters to return text translate, for just one parameter return
     * translated code, if there is more than one parameter. transform string will be format with
     * function sprintf
     *
     * Language file:
     * [eng]
     * user.greeting = 'Hello, %s!'
     *
     * Script:
     * $dic = $app->lang();
     * $dic->text('user.greeting', $app->user()->getName());
     *
     * @return mixed
     */
    public function text()
    {
        $code = trim(func_get_arg(0));
        if(func_num_args() > 1) {
            if(array_key_exists($code, $this->_content)) {
                $data = func_get_args();
                $data[0] = $this->_content[$code];
                return call_user_func_array('sprintf', $data);
            } else {
                return $code;
            }
        } else {
            return (array_key_exists($code, $this->_content)) ? $this->_content[$code] : $code;
        }
    }

    /**
     * Define a translate array
     *
     * @param $arr - Массив [код => перевод, ...]
     */
    public function setList($arr)
    {
        $this->_content = $arr;
    }

    /**
     * Return translation list
     *
     * @return array
     */
    public function getList()
    {
        return $this->_content;
    }
}