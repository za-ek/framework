<?php
namespace Zaek\Kernel;
use Zaek\Kernel\Exception\IncorrectBufferOrder;

/**
 * Output buffering for delaying output and delayed function execution
 * @package Zaek\Kernel
 */
class Buffer
{
    /**
     * Variables to be replaced
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Variables hash
     *
     * @var array
     */
    protected $_params_hash = array();

    protected $_callable_params = array();

    /**
     * Buffer content
     *
     * @var string
     */
    protected $_content;

    /**
     * If content was treated
     *
     * @var boolean
     */
    private $_treated = false;

    /**
     * Is content treating enabled
     *
     * @var bool
     */
    private $_enabled = true;

    /**
     * @var int
     */
    private $_level = -1;

    /**
     * Callback function list
     *
     * @var array
     */
    protected $_cb = array();

    function __construct ()
    {
    }

    /**
     * Buffer initialization - turn output buffering on
     *
     * @return $this
     */
    public function start ()
    {
        ob_start(array($this, 'callbackEnd'));
        $this->_level = ob_get_level();
        return $this;
    }

    /**
     * Callback function when output buffering is turning off
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
     * Show up an anchor for delayed output
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
     * Setting up delayed value $v for variable $k
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
     * Show up an anchor for delayed function call
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
     * This feature is for replace text $k in whole content by value $v
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
     * Stop output buffering and replace all delayed values and functions
     *
     * @return string
     */
    public function end ()
    {
        if ( $this->_level == ob_get_level() ) {
            $this->_content = \ob_get_clean();

            foreach ($this->_params as $k => $v) {
                try {
                    if (is_callable($v)) {
                        $buffer = new Buffer();
                        $buffer->start();

                        call_user_func_array($v, array($this->_callable_params[$k]));

                        $buffer->end();
                        $this->_params[$k] = $buffer->getContent();
                    }
                } catch (Exception $e) {

                }
                if (array_key_exists($k, $this->_cb)) {
                    $buffer = new Buffer();
                    $buffer->start();

                    call_user_func_array($this->_cb[$k], array($this->_params[$k]));

                    $buffer->end();
                    $this->_params[$k] = $buffer->getContent();
                }
            }

            $this->_treatContent();
            $this->disable();
            $this->_started = false;
        } else {
            throw new IncorrectBufferOrder();
        }

        return $this->_content;
    }

    /**
     * Show up buffer content
     *
     */
    public function show ()
    {
        $this->_treatContent();

        print $this->_content;
    }

    /**
     * return buffer content after treating
     *
     * @return string
     */
    public function getContent()
    {
        $this->_treatContent();

        return $this->_content;
    }

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
     * Disable delayed output but still buffering
     */
    public function disable()
    {
        $this->_enabled = false;
    }
    public function enabled()
    {
        return $this->_enabled;
    }

    public function __toString()
    {
        return $this->_content;
    }

    /**
     * Return current value of delayed variable, this method will not execute functions as value,
     * to get calculated value you need to call method end() before.
     *
     * @param $k
     * @return bool
     */
    public function getValue($k)
    {
        return (array_key_exists($k, $this->_params)) ? $this->_params[$k] : false;
    }

    /**
     * Empty buffered content
     */
    public function clear()
    {
        $this->_content = '';
    }

    /**
     * Add replacement
     *
     * @param $code
     * @param $val
     */
    public function push($code, $val)
    {
        if ( !isset($this->_params[$code]) ) $this->_params[$code] = [];

        array_push($this->_params[$code], $val);
    }
}
