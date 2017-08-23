<?php
namespace zaek\basics\algorithm;

class COverride extends \SplDoublyLinkedList
{
    public function rollMethod($method_name, $params = array())
    {
        $this->rewind();

        while ( $this->valid() ) {
            $result = $this->callMethod($method_name, $params);

            $this->next();

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
}