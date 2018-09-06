<?php
namespace Zaek\User;

use Zaek\Engine\Main;

class User
{
    /**
     * @var Main
     */
    protected $_app;
    /**
     * @var Access
     */
    protected $_access;

    /**
     * @var Language
     */
    protected $_language = null;

    public function __construct(Main $app)
    {
        $this->_app = $app;
    }
    public function access()
    {
        if ( is_null( $this->_access) ) {
            $this->_access = new Access($this->_app);
        }

        return $this->_access;
    }

    public function lang()
    {
        if ( is_null($this->_language) ) {
            $this->_language = new Language($this->_app);
        }

        return $this->_language;
    }
    public function can($action)
    {
        return $this->access()->can($action);
    }
}