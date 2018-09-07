<?php
namespace Zaek\Kernel;

use Zaek\Engine\Main;

class Router
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
     * @param $uri
     * @return string
     */
    public function uri($uri)
    {
        return $uri;
    }
}