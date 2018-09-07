<?php
namespace Zaek\Kernel;

use Zaek\Engine\Main;

class Request
{
    /**
     * @var Main
     */
    protected $_app;

    /**
     * Request constructor.
     * @param Main $app
     */
    public function __construct(Main $app)
    {
        $this->_app = $app;
    }
}