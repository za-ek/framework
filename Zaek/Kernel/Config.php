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
     * Class constructor receive a configuration array:
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
     * Return config param $opt from section $sec, throw an exception if there is no
     * section or no option in configuration
     *
     * @param string $sec
     * @param string $opt
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
     * Check if there is a section $sec and option $opt in current config
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

    /**
     * Return an array of config data
     *
     * @return array
     */
    public function getArray()
    {
        return $this->_data;
    }

    /**
     * Set directly an array of config data
     *
     * @param array $arr
     * @return $this
     */
    public function setArray(array $arr)
    {
        $this->_data = $arr;
        return $this;
    }
}