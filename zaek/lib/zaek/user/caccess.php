<?php
namespace zaek\user;

use zaek\engine\CMain;
use zaek\kernel\CException;

class CAccess
{
    protected $_app;

    public function __construct(CMain $app)
    {
        $this->_app = $app;
    }

    /**
     * Для генерации хэша в консоли:
     * php -r 'echo hash("sha256", "логинпарольсоль")'
     *
     * @param $login
     * @param $password
     * @param $salt
     * @return string
     */
    protected function hash($login, $password, $salt)
    {
        return hash('sha256', $login. $salt . $password);
    }

    public function authenticate($login, $password)
    {
        // Буффер должен быть отключен
        if ( $this->_app->template()->enabled() ) {
            throw new CException('CAN_NOT_CHANGE_HEADERS_UNTIL_BUFFER_IS_RUNNING');
        }

        $aUser = $this->_app->data()->select('users', ['login' => $login], ['hash','salt','id','login'])->fetch();

        if ( $aUser ) {
            if ( $this->hash($login, $password, $aUser[1]) == $aUser[0] ) {
                if ( session_status() != PHP_SESSION_ACTIVE ) {
                    session_start();
                }
                // Аутентификация пройдена
                $_SESSION['z_auth_user'] = [
                    'id' => $aUser[2],
                    'login' => $aUser[3],
                ];
            }
        }
    }

    public function logout()
    {
        if ( session_status() != PHP_SESSION_ACTIVE ) {
            session_start();
        }

        unset($_SESSION['z_auth_user']);
    }

    /**
     * Проверяет, можно ли выполнить дейтвие $action текущему пользователю
     * @param $action
     * @return bool
     */
    public function can($action)
    {
        if ( session_status() != PHP_SESSION_ACTIVE ) {
            session_start();
        }

        if ( isset($_SESSION['z_auth_user']) ) {
            return true;
        } else {
            return false;
        }
    }
}