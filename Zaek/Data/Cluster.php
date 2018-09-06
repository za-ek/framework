<?php
namespace Zaek\Data;

use Zaek\Engine\Main;
use Zaek\Kernel\Table;

class Cluster
{
    protected $_app;
    protected $_list = [];

    public function __construct(Main $app)
    {
        $this->_app = $app;
    }

    public function connect($id, Connector $connector, $bOverwrite = false)
    {
        if ( !isset($this->_list[$id]) || $bOverwrite ) {
            $this->_list[$id] = $connector;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \DomainException
     */
    public function get($id)
    {
        if ( isset($this->_list[$id]) ) {
            return $this->_list[$id];
        } else {
            throw new \DomainException('Connection not found');
        }
    }
}