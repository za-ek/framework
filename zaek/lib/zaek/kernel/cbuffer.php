<?php
namespace zaek\kernel;
/**
 * @ru Буферизация вывода для отложенного вывода значений и отложенного
 * выполнения функций.
 *
 * @en Output buffering for delaying output and delayed function execution
 *
 * @es Almacenamiento en búfer de la salida para adelantar la salida y la ejecución
 * de algunos funciones
 */

class CBuffer
{
    /**
     * @en Variables to be replaced
     * @es Lista de variables que han de ser reemplazados
     * @ru Список переменных для замены
     *
     * @var array
     */
    protected $_params = array();

    /**
     * @en Variables hash
     * @es Lista de los hash de variables
     * @ru Список хэшей для переменных
     *
     * @var array
     */
    protected $_params_hash = array();

    protected $_callable_params = array();

    /**
     * @en Buffer content
     * @es Contenido de búfer
     * @ru Содержание буфера
     *
     * @var string
     */
    protected $_content;

    /**
     * @en If content was treated
     * @es Si el contenido de búfer ya fue tratado
     * @ru Указывает, была ли выполнена обработка содержимого
     *
     * @var boolean
     */
    private $_treated = false;

    /**
     * @en Is content treating enabled
     * @es Si está activado el tratamiento del contenido
     * @ru Включена ли обработка буфера
     *
     * @var bool
     */
    private $_enabled = true;

    /**
     * @en Callback function list
     * @es Lista de funciones de callback
     * @ru Список callback-функций
     *
     * @var array
     */
    protected $_cb = array();

    function __construct ()
    {
    }

    /**
     * @en Buffer initialization - turn output buffering on
     * @es Inicialización del búfer - activa el almacenamiento de la salida en búfer
     * @ru Инициализация буфера - включает буферизацию
     *
     * @return $this
     */
    public function start ()
    {
        ob_start(array($this, 'callbackEnd'));
        return $this;
    }

    /**
     * @en Callback function when output buffering is turning off
     * @es Función callback cuando se termine la buferización
     * @ru callback-функция для завершения буферизации
     *
     * @param string $b
     * @return string
     */
    public function callbackEnd($b)
    {
        if ( !$this->_treated ) {
            $this->_content = $b;
            $this->_treatContent();
        }

        return $this->_content;
    }

    /**
     * @en Show up an anchor for delayed output
     * @es Pone un anchor que sea reemplazada al terminar buferización
     * @ru Выводит якорь для отложенного вывода значения
     *
     * @param string $name
     */
    public function showValue($name)
    {
        if($this->_enabled) {
            // на случай указания значения до вызова показа
            if (!array_key_exists($name, $this->_params))
                $this->_params[$name] = "";

            $this->_params_hash[$name] = md5($name) . hash("adler32", $name);
            echo $this->_params_hash[$name];
        } else if (array_key_exists($name, $this->_params)) {
            echo $this->_params[$name];
        }
    }

    /**
     * @en Setting up delayed value $v for variable $k
     * @es Establece el valor $v de la variable $k
     * @ru Устанавливает значение ($v) для отложенного вывода ($k)
     *
     * @param string $k
     * @param mixed $v
     * @param array $params
     */
    public function setValue($k, $v, $params = array())
    {
        $this->_params[$k] = $v;
        $this->_callable_params[$k] = $params;
        $this->_treated = false;
    }

    /**
     * @en Show up an anchor for delayed function call
     * @es Pone un anchor a la salida para función retrasada
     * @ru Выводит якорь для отложенной функции
     *
     * @param $name
     * @param $func
     */
    public function showFunction($name, $func)
    {
        if($this->_enabled) {

            $this->_cb[$name] = $func;
            if (!array_key_exists($name, $this->_params)) {
                $this->_params[$name] = array();
            }

            $this->_params_hash[$name] = md5($name) . hash("adler32", $name);
            echo $this->_params_hash[$name];
        } else if (array_key_exists($name, $this->_params)) {
            call_user_func_array($func, $this->_params[$name]);
        }
    }

    /**
     * @en This feature is for replace text $k in whole content by value $v
     * @es Esta función permite cambiar todas cadenas $k por el $v en el contenido del búfer
     * @ru Заменяет в буфере все найденные строки $k на $v
     *
     * @param $k
     * @param $v
     */
    public function setRawValue($k, $v)
    {
        $this->_params[$k] = $v;
        $this->_params_hash[$k] = $k;
    }

    /**
     * @en Stop output buffering and replace all delayed values and functions
     * @es Deja el almacenamiento de la salida en el búfer y reemplaza todos los valores y funciones retrasados
     * @ru Останавливает буферизацию вывода и заменяет все отложенные значения и функции
     *
     * @return string
     */
    public function end ()
    {
        $this->_content = \ob_get_clean();

        foreach($this->_params as $k => $v) {
            try {
                if(is_callable($v)) {
                    $buffer = new CBuffer();
                    $buffer->start();

                    call_user_func_array($v, array($this->_callable_params[$k]));

                    $buffer->end();
                    $this->_params[$k] = $buffer->getContent();
                }
            } catch ( CException $e ) {

            }
            if(array_key_exists($k, $this->_cb)) {
                $buffer = new CBuffer();
                $buffer->start();

                call_user_func_array($this->_cb[$k], array($this->_params[$k]));

                $buffer->end();
                $this->_params[$k] = $buffer->getContent();
            }
        }

        $this->_treatContent();
        $this->disable();

        return $this->_content;
    }

    /**
     * @en Show up buffer content
     * @es Envia el contenido del búfer a la salida
     * @ru Показывает содержимое буфера
     *
     */
    public function show ()
    {
        $this->_treatContent();

        print $this->_content;
    }

    /**
     * @en return buffer content after treating
     * @es Devuelve el contenido del búfer despues de tratarlo
     * @ru Возвращает содержимое буфера после обработки
     *
     * @return string
     */
    public function getContent()
    {
        $this->_treatContent();

        return $this->_content;
    }

    /**
     * Обработка буффера
     */
    private function _treatContent ()
    {
        if($this->_enabled) {
            $aSearch = array();
            $aReplace = array();
            foreach( $this->_params_hash as $k => $v ) {
                $aSearch[] = $v;
                if(is_array($this->_params[$k])) {
                    $aReplace[] = $k;
                } elseif(is_callable($this->_params[$k])) {
                    $aReplace[] = $this->_params[$k];
                } elseif (
                    ( !is_array( $this->_params[$k] ) ) &&
                    ( ( !is_object( $this->_params[$k] ) && settype( $this->_params[$k], 'string' ) !== false ) ||
                        ( is_object( $this->_params[$k] ) && method_exists( $this->_params[$k], '__toString' ) ) )
                ) {
                    $aReplace[] = $this->_params[$k];
                } else {
                    $aReplace[] = '';
                }
            }
            $this->_content = str_replace($aSearch, $aReplace, $this->_content);
        }
        $this->_treated = true;
    }

    /**
     * @en Disable delayed output but still buffering
     * @es Deshabilita la salida retrasada pero no para el buferización
     * @ru Выключает отложенный вывод, но оставляет рабочим буферизацию
     */
    public function disable()
    {
        $this->_enabled = false;
    }

    public function __toString()
    {
        return $this->_content;
    }

    /**
     * @en Return current value of delayed variable, this method will not execute functions as value,
     * to get calculated value you need to call method end() before.
     *
     * @es Devuelve el valor actual de variable $k, no ejecuta los valores definidas con funciones,
     * para conseguir estos valores primero llama al metodo get()
     *
     * @ru Возвращает значение переменной буфера, этот метод не рассчитывает значения заданные функциями (для
     * получения таких значений необходимо вызвать метод get())
     *
     * @param $k
     * @return bool
     */
    public function getValue($k)
    {
        return (array_key_exists($k, $this->_params)) ? $this->_params[$k] : false;
    }
    public function clear()
    {
        $this->_content = '';
    }
}
