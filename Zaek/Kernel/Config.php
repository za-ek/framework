<?php
namespace Zaek\Kernel;


use Zaek\Kernel\Exception\ConfigSectionNotSet;
use Zaek\Kernel\Exception\ConfigValueNotSet;

final class Config
{
    /**
     * Config values
     *
     * @var array
     */
    protected $_data    =  array();

    public function __construct(array $arr = [])
    {
        $this->_data = $arr;
    }

    /**
     * @en Class constructor receive a configuration array:
     * array(
     *  'section1' => array(
     *      'option1.1' => '...',
     *      'option1.2' => '...',
     *  ),
     *  'section2' => array(
     *      'option2.1' => '...',
     *      ...
     *  ),
     *  ...
     * )
     *
     * @es Constructor de clase, admite un array de la siguiente formato:
     * array(
     *  'secсión1' => array(
     *      'opción1.1' => '...',
     *      'opción1.2' => '...',
     *  ),
     *  'secсión2' => array(
     *      'opción2.1' => '...',
     *      ...
     *  ),
     *  ...
     * )
     *
     * @ru Конструктор класса, входящий массив должен содержать ключи и значения в
     * виде массива со структурой:
     * array(
     *  'раздел' => array(
     *      'опция1.1' => '...',
     *      'опция1.2' => '...',
     *  ),
     *  'раздел' => array(
     *      'опция2.1' => '...',
     *      ...
     *  ),
     *  ...
     * )
     * .
     * @param $arr
     */

    public function override(array $arr)
    {
        $this->_data = array_replace_recursive(
            $this->_data + $arr,
            $arr
        );
    }

    /**
     * @en Return config param $opt from section $sec, throw an exception with code=3 if there is no
     * section or no option in configuration
     *
     * @es Devuelve el valor de opción $opt en sección $sec, si la sección o el parametro
     * no existen lanza una excepción con código 3
     *
     * @ru Возвращает параметр файла конфигурации, если не найден ключ или параметр -
     * выбросит исключение с кодом ошибки 3
     *
     * @param string $sec
     * @param string $opt
     * @throws Exception
     * @return mixed Значение опции конфигурации
     */
    public function getValue($sec, $opt)
    {
        // если не присутствует раздел
        if(strlen($sec) > 0 && !array_key_exists($sec, $this->_data)) {
            throw ConfigSectionNotSet::create($sec);
        }

        // опция в разделе
        if( array_key_exists($opt, $this->_data[$sec]) ) {
            return $this->_data[$sec][$opt];
        } else {
            throw ConfigValueNotSet::create($sec, $opt);
        }
    }

    /**
     * @en Check if there is a section $sec and option $opt in current config
     * @es Comprueba si hay sección $sec y directiva $opt en esta configuración
     * @ru Проверяет наличие раздела $sec и параметра $opt в текущей конфигурации
     *
     * @param string $sec
     * @param string $opt
     * @return boolean
     */
    public function isDefined($sec, $opt)
    {
        if(array_key_exists($sec, $this->_data)) {
            if(array_key_exists($opt, $this->_data[$sec])) {
                return true;
            }
        }
        return false;
    }
    public function getArray()
    {
        return $this->_data;
    }
    public function setArray(array $arr)
    {
        $this->_data = $arr;
        return $this;
    }
}