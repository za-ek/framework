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
     * Аутентификация email и паролем
     * @param $email
     * @param $password
     * @return bool
     * @throws CException
     */
    public function authenticateByEmail($email, $password)
    {
        $aUser = $this->_app->data()->select('users', ['email' => $email], ['salt', 'login'])->fetch();
        return $this->authenticateBy([
            'login' => $aUser[1],
            'hash' => $this->hash($aUser[1], $password, $aUser[0])
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
                ),1, 32
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
            $sс = $this->_app->conf()->get('user', 'only_https');

            if ( !is_array($sd) ) {
				if ( !$sd ) {
					$sd = $this->_app->conf()->get('request','host');
				}
                $sd = [$sd];
            }

            foreach ( $sd as $domain ) {
                setcookie('hash', $hash, time() + $lt, '/', $domain, $sс);
                setcookie('login', $login, time() + $lt, '/', $domain, $sс);
                setcookie('session_hash', $session_hash, time() + $lt, '/', $domain, $sс);
            }

            return true;
        } else {
            return false;
        }
    }

    public function logout()
    {
		$sd = $this->_app->conf()->get('user', 'session_domain');
		$sс = $this->_app->conf()->get('user', 'only_https');

		if ( !is_array($sd) ) {
			if ( !$sd ) {
				$sd = $this->_app->conf()->get('request','host');
			}
			$sd = [$sd];
		}

		foreach ( $sd as $domain ) {
			setcookie('hash', '', 0, '/', $domain, $sс);
			setcookie('login', '', 0, '/', $domain, $sс);
			setcookie('session_hash', '', 0, '/', $domain, $sс);
		}
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
	public function isAuthenticated()
	{
		$user_hash  = $_COOKIE['hash'] ?? '';
        $session_hash = $_COOKIE['session_hash'] ?? '';
        $login = $_COOKIE['login'] ?? '';
		
		if($user_hash !== false && $session_hash !== false && $login !== false) {
			return $this->_app->data()->select('users_auth', ['user_login' => $login, 'user_hash' => $user_hash, 'session_hash' => $session_hash], ['id'], ['date_create' => 'ASC'], 1)->fetch();
        }
		
		return false;
	}
	public function getCurrent()
    {
        if( $this->isAuthenticated() ) {
			return $this->_app->data()->select('users', ['login' => $_COOKIE['login']], ['id','email'])->fetch(\zaek\kernel\CTable::FETCH_ASSOC);
        } else {
			return false;
		}
    }
}
