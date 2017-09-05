<?php
namespace zaek\user;

use zaek\engine\CMain;

class CUser
{
    protected $_app;
    /**
     * @var CAccess
     */
    protected $_access;

    public function __construct(CMain $app)
    {
        $this->_app = $app;
    }
    public function access()
    {
        if ( is_null( $this->_access) ) {
            $this->_access = new CAccess($this->_app);
        }

        return $this->_access;
    }
}