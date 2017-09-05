<?php
namespace zaek\data;

use zaek\engine\CMain;
use zaek\kernel\CException;
use zaek\kernel\CTable;

class CCluster
{
    protected $_app;
    protected $_list = [];

    public function __construct(CMain $app)
    {
        $this->_app = $app;
    }

    public function connect($id, CConnector $connector, $bOverwrite = false)
    {
        if ( !isset($this->_list[$id]) || $bOverwrite ) {
            $this->_list[$id] = $connector;
            return true;
        } else {
            return false;
        }
    }

    public function get($id)
    {
        if ( isset($this->_list[$id]) ) {
            return $this->_list[$id];
        } else {
            throw new CException('NO_SUCH_CONNECTION');
        }
    }
}