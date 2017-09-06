<?php
namespace zaek\kernel;

use zaek\engine\CMain;

class CDictionary
{
    /**
     * @var array
     */
    protected $_content = array();

    protected $_lang = null;
    protected $_app = null;

    /**
     * CDictionary constructor.
     * @param CMain $app
     */
    public function __construct(CMain $app)
    {
        $this->_app = $app;
    }

    /**
     * @en Add translations from a ini file located at $path
     * @es Añade la lista de traducciones de un fichero tipo 'ini' de la ruta $path
     * @ru Добавляет список переводов из ini-файла $path
     *
     * @param $path
     * @throws CException
     */
    public function addFile($path)
    {
        $path = str_replace('%LANGUAGE%', $this->getLang(), $this->_app->fs()->convertPath($path));

        if ( $this->_app->fs()->checkRules($path, CFile::MODE_R) ) {
            $content = (array)@parse_ini_file($path, true);
            if ( array_key_exists($this->getLang(), $content) && is_array($content[$this->getLang()]) ) {
                $this->_content = array_merge($this->_content, $content[$this->getLang()]);
            } else {
                $this->_content = array_merge($this->_content, (array)$content);
            }
        } else {
            throw new CException('LANGUAGE_FILE_DOES_NOT_EXIST ['.$path.']', 4);
        }
    }

    /**
     * @en Receive one or more parameters to return text translate, for just one parameter return
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
     * @es Devuelve el text traducido, al pasarle mas que un argumento la cadena (primer argumento)
     * será tratada con función sprintf
     *
     * Fichero de lenguaje:
     * [esp]
     * user.greeting = 'Hola, %s!'
     *
     * Script:
     * $dic = $app->lang();
     * $dic->text('user.greeting', $app->user()->getName());
     *
     * @ru Возвращает перевод текста, если передать методу больше одного параметра, то он использует
     * их для обработки строки с помощью функции sprintf
     *
     * Языковой файл:
     * [rus]
     * user.greeting = 'Привет, %s!'
     *
     * Скрипт:
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
     * @en Return using language
     * @es Devuelve el lenguaje
     * @ru Возвращает код языка
     *
     * @return string
     */
    public function getLang()
    {
        if ( $this->_lang === null ) {
            $this->_lang = $this->_app->user()->lang()->getCode();
        }

        return $this->_lang;
    }

    /**
     * Устанавливает массив словаря
     *
     * @param $arr - Массив [код => перевод, ...]
     */
    public function setList($arr)
    {
        $this->_content = $arr;
    }

    /**
     * Возвращает массив словаря
     *
     * @return array
     */
    public function getList()
    {
        return $this->_content;
    }
}