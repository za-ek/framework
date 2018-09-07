<?php
namespace Zaek\Kernel;

use Zaek\Engine\Main;

class Response
{
    /**
     * @var Main
     */
    protected $_app;

    /**
     * @var string
     */
    protected $_content = null;

    public function __construct(Main $app)
    {
        $this->_app = $app;
    }
    /**
     * @param $content
     * @return Response
     */
    public function setBody($content)
    {
        $this->_content = $content;

        return $this;
    }

    /**
     * Print response
     */
    public function send()
    {
        print $this->_content;
    }
}