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
    public function hash($login, $password, $salt)
    {
        return hash('sha256', $login. $salt . $password);
    }

    /**
     * Аутентификация логином и паролем
     * @param $login
     * @param $password
     * @return bool
     * @throws CException
     */
    public function authenticate($login, $password)
    {
        $aUser = $this->_app->data()->select('users', ['login' => $login], ['salt'])->fetch();
        return $this->authenticateBy([
            'login' => $login,
            'hash' => $this->hash($login, $password, $aUser[0])
        ]);
    }

    /**
     * Аутентификация пользователя найденного по фильтру $aFilter
     *
     * @param $aFilter
     * @return bool
     * @throws CException
     */
    public function authenticateBy($aFilter)
    {
        $aUser = $this->_app->data()->select('users', $aFilter, ['login','hash'])->fetch();

        if ( $aUser ) {
            // Аутентификация пройдена
            return $this->_makeAuth($aUser[0],$aUser[1]);
        } else {
            throw new CException('USER_NOT_FOUND');
        }
    }

    /**
     * Производит аутентификацию пользователя: сохраняет параметры в cookie,
     *  использует таблицу "users_auth" с полями id, user_login, user_hash, session_hash,
     *  date_create, ip
     *
     * @param $login
     * @param $hash
     * @return bool
     */
    protected function _makeAuth($login, $hash)
    {
        $oLines = $this->_app->data()->select('users_auth', [
            'user_login' => $login,
            'user_hash' => $hash,
        ], ['id', 'session_hash'], ['date_create' => 'desc']);

        if ( $oLines->getLength() >= $this->_app->conf()->get('user', 'session_cnt_limit') ) {
            $aSessionHash = $oLines->fetch();
            $session_hash = $aSessionHash[1];

            $this->_app->data()->update('users_auth', [
                'date_create' => date('Y-m-d H:i:s'),
            ], ['id' => $aSessionHash[0]]);
        } else {
            $session_hash = substr(
                str_shuffle(
                    str_repeat(
                        $x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                        1
                    )
                ),1, 12
            );

            $this->_app->data()->insert('users_auth', [
                'user_login' => $login,
                'user_hash' => $hash,
                'date_create' => date('Y-m-d H:i:s'),
                'session_hash' => $session_hash,
                'ip' => $this->_app->conf()->get('client', 'ip')
            ]);
        }

        if ( $session_hash ) {
            $lt = $this->_app->conf()->get('user', 'session_lifetime');
            $sd = $this->_app->conf()->get('user', 'session_domain');

            if ( !is_array($sd) ) {
                $sd = [$sd];
            }

            foreach ( $sd as $domain ) {
                setcookie('hash', $hash, time() + $lt, '/', $domain, true);
                setcookie('login', $login, time() + $lt, '/', $domain, true);
                setcookie('session_hash', $session_hash, time() + $lt, '/', $domain, true);
            }

            return true;
        } else {
            return false;
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