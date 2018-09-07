<?php
namespace Zaek\Data;

use Zaek\Engine\Main;
use Zaek\Kernel\Table;

abstract class Connector
{
    protected $_app;

    public function __construct(Main $app)
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
     * @return Table
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