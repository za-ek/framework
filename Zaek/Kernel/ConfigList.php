<?php
namespace Zaek\Kernel;

use Zaek\Basics\Algorithm\Override;
use Zaek\Engine\Main;
use Zaek\Kernel\Exception\ConfigValueNotSet;

class ConfigList extends Override
{
    /**
     * @var Main
     */
    protected $_app;

    public function __construct(Main $app)
    {
        $this->_app = $app;
    }

    /**
     * Return config param $opt from section $sec from configuration list
     * @param $sec
     * @param $opt
     * @return bool|mixed
     */
    public function get($sec, $opt)
    {
        $result = $this->rollMethod('getValue', array($sec, $opt));
        if ( $result !== null ) {
            return $result;
        } else {
            throw ConfigValueNotSet::create($sec, $opt);
        }

    }

    /**
     * Return true if there is a parameter
     *
     * @param $sec
     * @param $opt
     * @return bool|mixed|null
     */
    public function isDefined($sec, $opt)
    {
        return $this->rollMethod('isDefined', array($sec, $opt));
    }
    /**
     * Config value could not be null
     * @param $result
     * @return bool
     */
    function check($result)
    {
        return ($result !== null);
    }

    /**
     *
     *
     * @param $method_name
     * @param $params
     * @return bool|mixed
     */
    protected function callMethod($method_name, $params)
    {
        try {
            if(!$this->current()) {
                return null;
            }

            if ( $this->current()[2] == null ) {
                $this->initializeConfig();
            }

            if ( call_user_func_array(array($this->current()[2], 'isDefined'), $params)) {
                return call_user_func_array(array($this->current()[2], $method_name), $params);
            } else {
                return null;
            }
        } catch ( \Exception $e ) {
            return false;
        }
    }

    protected function initializeConfig()
    {
        $a = $this->current();

        switch ($a[0]) {
            case 'ini':
                $a[2] = new Config((array)@parse_ini_file($a[1], true));
                break;
        }

        $this[$this->key()] = $a;
    }

    public function addFile($path, $type)
    {
        parent::push([$type, $path, null]);
    }
    public function push($value)
    {
        if ( is_array($value) ) {
            parent::push([
                'array',
                '',
                new Config($value)
            ]);
        } else if ( is_object($value) && $value instanceof Config ) {
            parent::push([
                '',
                '',
                $value
            ]);
        }
    }
}