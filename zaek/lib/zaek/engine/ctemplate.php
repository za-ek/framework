<?php
namespace zaek\engine;

use zaek\kernel\CBuffer;

class CTemplate extends CBuffer
{
    protected $_meta = [];
    protected $_js = [];
    protected $_css = [];

    protected $_app;

    public function __construct(CMain $app)
    {
        $this->_app = $app;
    }

    /**
     * @en Setting up css/js files and meta tags to template. If it starts with backslash "\" then path will be calculate
     * from template css/js directory.
     *
     * \index.js => /zaek/tpl/pages/default/js/index.js
     * /index.js => /relative_path/index.js
     * //www.za-ek.ru/index.js => //www.za-ek.ru/index.js
     * @es
     *
     * @ru Добавляет css/js файл в шаблон. Если путь начинается с обратного слэша "\", то расчитанный путь
     * будет начинаться с пути к css/js папки.
     *
     * \index.js => /zaek/tpl/pages/default/js/index.js
     * /index.js => /относительный_путь/index.js
     * //www.za-ek.ru/index.js => //www.za-ek.ru/index.js
     *
     * @param $type - css|js
     * @param $value - string|array
     * @param string $add_str
     * @return bool
     */
    public function addProp($type, $value, $add_str = '')
    {
        if ( $type == 'css' || $type == 'js' ) {
            if ( is_array($value) ) {
                foreach ( $value as $p ) {
                    return $this->addProp($type, $p, $add_str);
                }
            } else {
                if (strlen($value) > 0) {
                    if (substr($value, 0, 1) == '\\') {
                        $value =
                            $this->_app->conf()->get('template', 'template_root') . '/' .
                            $this->_app->conf()->get('template', 'code') .
                            '/' . $type . '/' . substr($value, 1);
                    } else if (
                        (substr($value, 0, 2) != '//') &&
                        (substr($value, 0, 4) != 'http')
                    ) {
                        $value = $this->_app->conf()->get('template', 'relative_path') . $value;
                    }
                }

                if ( (substr($value, 0, 4) != 'http') ) {
                    $value = 'http'.(($_SERVER['SERVER_PORT'] == 443 || isset($_SERVER['HTTPS']) || isset($_SERVER['HTTP_S'])) ? 's' : '' ).'://' . $_SERVER['SERVER_NAME'] . $value;
                }

                if ($type == 'css') {
                    $this->_css[] = array($value, $add_str);
                } else {
                    $this->_js[] = array($value, $add_str);
                }
            }

            return true;
        } else if ( in_array($type, array(
            'application-name', 'author', 'description', 'generator', 'keywords'
        )) ) {
            $this->_meta[$type] = array($value, $add_str);
            return true;
        } else if ( $type == 'meta' ) {
            $this->_meta[] = array($value, $add_str);
        } else if ( $type == 'title' ) {
            $this->setValue('page_title', $value);
        } else {
            return false;
        }
    }

    public function addJs($val, $add_str = '')
    {
        return $this->addProp('js', $val, $add_str);
    }
    public function addCss($val, $add_str = '')
    {
        return $this->addProp('css', $val, $add_str);
    }
    public function addMeta($type, $add_str = '')
    {
        return $this->addProp($type, $add_str);
    }
    public function setTitle($title)
    {
        $this->setValue('page_title', $title);
    }


    public function showProp($type)
    {
        if ( is_array($type) ) {
            foreach ( $type as $t ) {
                $this->showProp($t);
            }
        } else {
            $this->showFunction('ZSHOW' . $type . '()', array($this, 'cbShow' . ucfirst($type)));
        }
    }
    public function cbShowCss()
    {
        foreach ( $this->_css as $css ) {
            echo '<link rel="stylesheet" href="'.$css[0].'" type="text/css" '.$css[1].'/>';
        }
    }
    public function cbShowJs()
    {
        foreach ( $this->_js as $js ) {
            echo '<script type="text/javascript" src="' . $js[0] . '" ' . $js[1] . '></script>';
        }
    }
    public function cbShowMeta()
    {
        foreach ( $this->_meta as $type => $arr ) {
            echo '<meta name="' . $type . '" content="'.str_replace('"','\"',$arr[0]).'" '.$arr[1].'/>';
        }
    }

    public function showHeadHTML()
    {
        $this->showFunction('page_css', array(
            $this, 'cbShowCss'
        ));
        $this->showFunction('page_js', array(
            $this, 'cbShowJs'
        ));
        $this->showFunction('page_meta', array(
            $this, 'cbShowMeta'
        ));
    }
    public function img($img)
    {
        return $this->_app->conf()->get('template', 'template_root') . '/' .
            $this->_app->conf()->get('template', 'code') . '/images/'. $img;
    }
    public function getCss()
    {
        return $this->_css;
    }
    public function getJs()
    {
        return $this->_js;
    }
    public function getMeta()
    {
        return $this->_meta;
    }
}