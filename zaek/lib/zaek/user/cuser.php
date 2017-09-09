<?php
namespace zaek\user;

use zaek\engine\CMain;

class CUser
{
    /**
     * @var CMain
     */
    protected $_app;
    /**
     * @var CAccess
     */
    protected $_access;

    /**
     * @var CLanguage
     */
    protected $_language = null;

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

    public function lang()
    {
        if ( is_null($this->_language) ) {
            $this->_language = new CLanguage($this->_app);
        }

        return $this->_language;
    }
    public function can($action)
    {
        return $this->access()->can($action);
    }
}