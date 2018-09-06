<?php
namespace Zaek\Kernel;

class Hooks
{
    private static $_LIST = array();

    /**
     * Вызов ранее установленного события
     *
     * @param string $name имя события
     * @param null $arg
     * @return bool
     */
    public static function trigger($name, $arg = null)
    {
        if( is_array(self::$_LIST) && array_key_exists($name, self::$_LIST)) {
            $result = false;

            foreach ( self::$_LIST[$name] as $func ) {
                $result = call_user_func_array($func, [$arg]);
                next(self::$_LIST[$name]);
            }

            return $result;
        }

        return false;
    }

    /**
     * @param array|string $aNames имена событий
     */

    public static function bind($aNames, $a)
    {
        if ( !is_array($aNames) ) $aNames = [$aNames];

        foreach ( $aNames as $name ) {
            if (!array_key_exists($name, self::$_LIST)) {
                self::$_LIST[$name] = array();
            }

            array_push(self::$_LIST[$name], $a);
        }
    }

    /**
     * удаляет обработчик события с указанным названием
     *
     * @param string $name Название события
     *
     */
    public static function unbind($name)
    {
        if ( isset(self::$_LIST[$name]) ) {
            unset(self::$_LIST[$name]);
        }
    }

    /**
     * Удаляет все события
     */
    public static function flush()
    {
        self::$_LIST = [];
    }

    /**
     * Проверяет, установлено ли событие $name
     *
     * @param $name
     * @return bool
     */
    public static function isBinded($name)
    {
        return array_key_exists($name, self::$_LIST);
    }
}