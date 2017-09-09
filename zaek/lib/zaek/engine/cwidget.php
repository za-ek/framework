<?php
namespace zaek\engine;

use zaek\kernel\CConfigList;

class CWidget extends CMain
{
    protected $_app;
    public function __construct(CMain $app, $uri)
    {
        $this->_app = $app;
        $this->_conf = clone $app->conf();
        $this->_conf->push([
            'template' => [
                'use_template' => false
            ],
            'request' => [
                'uri' => $uri
            ]
        ]);
    }
    public function run()
    {
        parent::run();

        foreach ( $this->template()->getCss() as $css ) {
            $this->_app->template()->addCss($css);
        }
        foreach ( $this->template()->getJs() as $js ) {
            $this->_app->template()->addJs($js);
        }
        foreach ( $this->template()->getMeta() as $type => $value ) {
            $this->_app->template()->addProp($type, $value[0], $value[1]);
        }
    }
}