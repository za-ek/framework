<?php
namespace zaek\data\mysqli;

use zaek\engine\CMain;
use zaek\kernel\CException;
use zaek\kernel\CTable;

class CConnector extends \zaek\data\CConnector
{
    protected $_link;

    public function __construct(CMain $app)
    {
        parent::__construct($app);
    }

    public function getLink()
    {
        if ( is_null($this->_link) ) {
            $this->_link = mysqli_connect(
                $this->_app->conf()->get('db_mysqli', 'server'),
                $this->_app->conf()->get('db_mysqli', 'user'),
                $this->_app->conf()->get('db_mysqli', 'password'),
                $this->_app->conf()->get('db_mysqli', 'db')
            );

            if ( !$this->_link ) {
                throw new CException('COULD_NOT_CONNECT_TO_DATABASE');
            }
        }
        return $this->_link;
    }

    /**
     * Выборка данных
     *
     * @param $type
     * @param array $aFilter
     * @param array $aRange
     * @param array $aOrder
     * @param array $aLimit
     * @return CTable
     * @throws CException
     */
    public function select($type, $aFilter = [], $aRange = [], $aOrder = [], $aLimit = [])
    {
        $table = new CResult();

        if ( $aRange ) {
            foreach ($aRange as $k => $v) {
                if (is_integer($k)) {
                    $aRange[$k] = "`{$v}` as `{$v}`";
                } else {
                    $aRange[$k] = "`{$k}` as `{$v}`";
                }
            }
        } else {
            $aRange = ['*'];
        }

        $aValues = [];
        if ( $aFilter ) {
            foreach ($aFilter as $k => $v) {
                $aFilter[$k] = "`{$k}` = ?";
                $aValues[] = &$v;
            }
        } else {
            $aFilter = [1];
        }

        if ( $aOrder ) {
            foreach ($aOrder as $field => $order) {
                $aOrder[$field] = "`{$field}` {$order}";
            }

            $order = " ORDER BY " . implode(',', $aOrder);
        } else {
            $order = '';
        }

        if ( !is_array($aLimit) ) {
            $aLimit = [$aLimit];
        }
        if ( count($aLimit) == 1 || count($aLimit) == 2 ) {
            $limit = ' LIMIT ' . implode(',', $aLimit);
        } else {
            $limit = '';
        }

        $query = "SELECT " . implode(',', $aRange) . " FROM {$type} 
        WHERE " . implode(',', $aFilter) . $order . $limit;

        $stmt = mysqli_prepare($this->getLink(), $query);
        if ( $stmt ) {
            if ( count($aValues) ) {
                call_user_func_array(
                    [$stmt, 'bind_param'],
                    array_merge([str_repeat('s', count($aValues))], $aValues)
                );
            }

            $result = $stmt->execute();
            if ($result) {
                $result = $stmt->get_result();
                $table->setMysqliResult($result);
            }
        } else {
            throw new CException($this->getLink()->error . "[{$query}]");
        }

        return $table;
    }

    /**
     * Добавление строки
     *
     * @param $type
     * @param $aData
     * @return mixed
     */
    public function insert($type, $aData)
    {
        // TODO: Implement insert() method.
    }

    /**
     * Удаление строк
     *
     * @param $type
     * @param array $aFilter
     * @param array $aOrder
     * @param array $aLimit
     * @return mixed
     */
    public function delete($type, $aFilter = [], $aOrder = [], $aLimit = [])
    {
        // TODO: Implement delete() method.
    }

    /**
     * Обновление строк
     *
     * @param $type
     * @param $aUpdate
     * @param array $aFilter
     * @param array $aOrder
     * @param array $aLimit
     * @return mixed
     */
    public function update($type, $aUpdate, $aFilter = [], $aOrder = [], $aLimit = [])
    {
        // TODO: Implement update() method.
    }
}