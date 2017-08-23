<?php
namespace zaek\kernel;

class CException extends \Exception
{
    protected static $ERRORS = array();
    /**
     * @en Symbolic error code (for language translation)
     * @es Código del error para ser traducido
     * @ru Символьный код ошибки для языкового файла
     *
     * @var string
     */
    protected $_s_code = '';

    /**
     * @en Function/class&method/module name caused the error
     * @es Nombre de función/módulo/clase y método en el que ha producido un error
     * @ru Функция/модуль/класс в котором произошла ошибка
     *
     * @var array
     */
    protected $_func    = array();

    /**
     * @en An array of additional information about the error
     * @es Un array de información adicional que ayuda a corregir el error
     * @ru Массив дополнительной информации которая помогает исправить ошибку
     *
     * @var array
     */
    protected $_add     = array();

    /**
     * @en Number of argument in error caused function/method
     * @es Número de argumento de función que ha producido el error
     * @ru Номер аргумента из-за которого произошла ошибка
     *
     * @var int
     */
    protected $_arg     = array();

    /**
     * @en Parse message by pattern: CODE (class::method, function, :::module) [/home/s, name] {2}
     *
     * There are:
     * CODE          - symbolic code of message to be translated must not contain an open bracket ("[", "(", "{")
     * class::method - class&method names that contains an error, must not contain an open bracket "(" or "{"
     * function      - function that contain an error, must not contain an open bracket "(" or "{"
     * :::module     - missing module name, must not contain an open bracket "(" or "{"
     * path:/home/s, name
     *               - additional strings that will be passed for translate (may be an associative)
     *                 must not contain an open brace "{"
     * 2             - number of the argument that need to be changed
     *
     * @es Analiza el mensaje segun el patrón: CÓDIGO (clase::método, función, :::módulo) [/home/s, nombre] {2}
     *
     * Aquí son:
     * CÓDIGO        - código del error para ser traducido, no puede contener el paréntesis de apertura "(,[,{"
     * clase::método - Los nombres de la clase y del método en los que ha producido el error, no puede contener
     *                 el corchete de apertura ni la llave "[,{"
     * function      - nombre de la función donde ha producido el error, no puede contener el corchete de apertura ni la llave "[,{"
     * :::module     - nombre del módulo que no está cargado, no puede contener el corchete de apertura ni la llave "[,{"
     * camino:/home/s, name
     *               - información suplementaria (pasarán a tradución como parametros), puede ser
     *                 expresada como un par de llave:valor, no puede contener la llave de apertura "{"
     * 2             - número del argumento que requiere cambio
     *
     * @ru Анализирует строку ошибки по шаблону: КОД (класс::метод, функция, :::модуль) [/home/s, имя] {2}
     *
     * Где:
     * КОД           - символьный код ошибки (для перевода), не может содержать скобки "(,[,{"
     * класс::метод - класс и метод в котором произошла ошибка, не может содержать скобки "[,{"
     * функция      - имя функции в которой произошла ошибка, не может содержать скобки "[,{"
     * :::модуль     - имя незагруженного модуля, не может содержать скобки "[,{"
     * путь:/home/s, name
     *               - дополнительная информация (будет передана аргументами к переводу),
     *                 может быть ассоциативным массивом
     * 2             - номер аргумента который стал причиной ошибки
     *
     * @param string $message - error message
     * @param int $code - error code (numeric)
     *
     */
    public function __construct($message, $code)
    {
        $pattern = "/^(?<code>[^([{]*)(?<func>[^[{]*)(?<add>[^\{]*)(?<arg>.*)$/";
        preg_match_all($pattern, $message, $arr);

        $sResult = $message;

        // error code
        if(count($arr['code']) > 0) {
            $this->_s_code = trim($arr['code'][0]);
        }

        // function/method argument
        if ( isset ($arr['arg']) && isset ($arr['arg'][0]) && strlen($arr['arg'][0]) > 0  ) {
            $this->_arg = explode(",", substr(trim($arr['arg'][0]), 1, -1));
            $this->_arg = array_map('trim', $this->_arg);
            $this->_arg = array_map('intval', $this->_arg);
        }

        $sResult = $this->getErrorDescription($this->_s_code) . PHP_EOL . $sResult . PHP_EOL;

        // Add link to help page for methods/functions that appears in error message
        if(count($arr['func']) > 0 && strlen($arr['func'][0]) > 0) {
            // remove brackets on trimmed string and split it by coma
            $aFunc = explode(",", substr(trim($arr['func'][0]), 1, -1));
            // trim particular string
            $aFunc = array_map('trim', $aFunc);

            for($i=0; $i < count($aFunc); $i++) {
                $src = $aFunc[$i];
                // missing module name
                if( strstr($aFunc[$i], ":::") ) {
                    $aFunc[$i] = array(
                        'type' => 'module',
                        'val'  => substr($aFunc[$i], 3)
                    );
                    // class&method
                } elseif( strstr($aFunc[$i], "::") !== false ) {

                    $tmp = explode("::", $aFunc[$i]);

                    $aFunc[$i] = array(
                        'type' => 'class_method',
                        'val'  => array(
                            $tmp[0],
                            $tmp[1]
                        )
                    );
                    // function
                } else {
                    $aFunc[$i] = array(
                        'type'  => 'function',
                        'val'   => trim($aFunc[$i])
                    );
                }

                $sResult = str_replace($src, "<a href='http://za-ek.ru/help/find.php?".http_build_query($aFunc[$i])."'>{$src}</a>", $sResult);
            }

            $this->_func = $aFunc;
        }

        $this->_add = array();
        // additional params "[]"
        if ( isset ( $arr['add'] ) && isset ( $arr['add'][0] ) && strlen($arr['add'][0]) > 0 ) {
            // removing brackets
            $arr['add'] = substr(trim($arr['add'][0]), 1, -1);
            // split argument list by coma
            $arr['add'] = explode(',', $arr['add']);

            foreach ( $arr['add'] as $str ) {
                $str = trim($str);

                if ( $index = strpos($str, ':') ) {
                    // associative
                    $this->_add[substr($str, 0, $index)] = substr($str, $index + 1);
                } else {
                    // indexed
                    $this->_add[] = $str;
                }
            }
        }

        parent::__construct($sResult, $code);
    }

    /**
     * @en Return error code obtained from error message
     * @es Devuelve el código del error obtenido desde el mensaje del error
     * @ru Возвращает символьный код ошибки
     *
     * @return string
     */
    public function getSymCode()
    {
        return $this->_s_code;
    }
    /**
     * @en Return function list obtained from error message
     * @es Devuelve lista de las funciones obtenido desde el mensaje del error
     * @ru Возвращает список проблемных функций
     *
     * @return string
     */
    public function getFunctions()
    {
        return $this->_func;
    }
    /**
     * @en Return additional error information obtained from error message
     * @es Devuelve información adicional del error obtenido desde el mensaje del error
     * @ru Возвращает дополнительные свойства ошибки
     *
     * @return array
     */
    public function getAdd()
    {
        return $this->_add;
    }
    /**
     * @en Return error argument number obtained from error message
     * @es Devuelve el número del argumento del error obtenido desde el mensaje del error
     * @ru Возвращает номер аргумента в функции с ошибкой
     *
     * @return string
     */
    public function getArg()
    {
        return $this->_arg;
    }

    protected function getErrorDescription($err)
    {
        return (array_key_exists($err, self::$ERRORS)) ? self::$ERRORS[$err] : $err;
    }
}
