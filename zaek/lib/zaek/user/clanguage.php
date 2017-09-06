<?php
namespace zaek\user;

use zaek\engine\CMain;

class CLanguage
{
    /**
     * @var array
     */
    protected $_lang_list;
    /**
     * @var string
     */
    protected $_language;
    /**
     * @var CMain
     */
    protected $_app;

    /**
     * CLanguage constructor.
     * @param CMain $app
     */
    public function __construct(CMain $app)
    {
        $this->_app = $app;
    }

    /**
     * @en Parse $_SERVER['HTTP_ACCEPT_LANGUAGE'] and return ordered list of supported languages
     * @es Analiza $_SERVER['HTTP_ACCEPT_LANGUAGE'] y devuelve una lista ordenada de lenguajes soportados
     * @ru Парсит $_SERVER['HTTP_ACCEPT_LANGUAGE'] и возвращает отсортированный список поддерживаемых языков
     *
     * @return array
     */
    public function getList()
    {
        if ( is_null($this->_lang_list) ) {
            $aLang = array();

            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" .
                    "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i",
                    $_SERVER['HTTP_ACCEPT_LANGUAGE'], $hits, PREG_SET_ORDER);

                $aLangFound = array();
                foreach ($hits as $arr) {
                    $langprefix = strtolower($arr[1]);
                    if (!empty($arr[3])) {
                        $langrange = strtolower($arr[3]);
                        $language = $langprefix . "-" . $langrange;
                    } else $language = $langprefix;
                    $qvalue = 1.0;
                    if (!empty($arr[5])) $qvalue = floatval($arr[5]);

                    $aLangFound[] = array($language, $qvalue);
                }

                usort($aLangFound, function ($a, $b) {
                    if ($a[1] == $b[1]) {
                        return 0;
                    }
                    return ($a[1] < $b[1]) ? -1 : 1;
                });

                foreach ($aLangFound as $l) $aLang[] = $l;
            }
            $this->_lang_list = array_reverse($aLang);
        }

        return $this->_lang_list;
    }

    /**
     * Возвращает код языка по ISO-639 (три символа)
     *
     * @return string
     */
    public function getCode()
    {
        if ( is_null($this->_language) ) {
            $this->setCode(@$this->getList()[0][0]);
            if ( !$this->_language ) {
                $this->setCode($this->_app->conf()->get('language', 'default'));
            }
        }
        return $this->_language;
    }

    /**
     * @param $lang
     */
    public function setCode($lang)
    {
        if ( strlen($lang) != 2 ) {
            $aMap = ['ru' => 'rus', 'en' => 'eng', 'en-us' => 'eng'];
            if ( array_key_exists($lang, $aMap) ) {
                $lang = $aMap[$lang];
            }
        }

        $this->_language = $lang;
    }
}