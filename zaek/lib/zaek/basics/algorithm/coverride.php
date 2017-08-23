<?php
namespace zaek\basics\algorithm;

class COverride implements \ArrayAccess, \Iterator
{
    private $_i;
    private $_count;
    private $_data;

    public function rewind()
    {
        $this->_i = -1;
    }
    public function next()
    {
        $this->_i++;
        if ( $this->_i < $this->_count ) {
            return true;
        } else {
            return false;
        }
    }
    public function prev()
    {
        $this->_i--;
        if ( $this->_i >= 0 ) {
            return true;
        } else {
            return false;
        }
    }
    public function reverse()
    {
        $this->_data = array_reverse($this->_data);
        $this->rewind();
    }
    public function current()
    {
        return $this->_data[$this->_i];
    }
    public function key()
    {
        return $this->_i;
    }
    public function push($v)
    {
        $this->_data[] = $v;
        $this->_count = count($this->_data);
    }

    public function rollMethod($method_name, $params = array())
    {
        $this->_i = $this->_count;

        while ( $this->prev() ) {
            $result = $this->callMethod($method_name, $params);

            if ( $this->check($result) ) {
                return $result;
            }
        }


        return false;
    }

    protected function callMethod($method_name, $params)
    {
        if ( is_array($method_name) ) {
            $result = null;
            foreach ( $method_name as $method ) {
                $result = call_user_func_array(array($this->current(), $method), $params);
            }
            return $result;
        } else {
            return call_user_func_array(array($this->current(), $method_name), $params);
        }
    }

    public function check($result)
    {
        return true;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
        $this->_count = count($this->_data);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
        $this->_count = count($this->_data);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->_i < $this->_count;
    }
}