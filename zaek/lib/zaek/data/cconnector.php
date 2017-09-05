<?php
namespace zaek\data;

use zaek\engine\CMain;
use zaek\kernel\CTable;

abstract class CConnector
{
    protected $_app;

    public function __construct(CMain $app)
    {
        $this->_app = $app;
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
     */
    abstract public function select($type, $aFilter = [], $aRange = [], $aOrder = [], $aLimit = []);

    /**
     * Добавление строки
     *
     * @param $type
     * @param $aData
     * @return mixed
     */
    abstract public function insert($type, $aData);

    /**
     * Добавление нескольких строк
     *
     * @param $type
     * @param $aKeys
     * @param $aData
     * @return mixed
     */
    public function insertMultiple($type, $aKeys, $aData)
    {
        foreach ( $aData as $line ) {
            $this->insert($type, array_combine($aKeys, $line));
        }
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
    abstract public function delete($type, $aFilter = [], $aOrder = [], $aLimit = []);

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
    abstract public function update($type, $aUpdate, $aFilter = [], $aOrder = [], $aLimit = []);
}