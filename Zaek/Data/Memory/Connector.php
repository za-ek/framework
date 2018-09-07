<?php
namespace Zaek\Data\Memory;

use Zaek\Kernel\Exception\ColumnCountMismatch;
use Zaek\Kernel\Table;

class Connector extends \Zaek\Data\Connector
{
    /**
     * @var Table[]
     */
    private static $_memory = [];
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
    public function select($type, $aFilter = [], $aRange = [], $aOrder = [], $aLimit = [])
    {
        if(!empty(self::$_memory[$type])) {
            $result = clone self::$_memory[$type];
            $result->setRow(0);
            $result->filter($aFilter);
            $result->sort($aOrder);
            return $result;
        } else {
            return new Table();
        }
    }

    /**
     * Добавление строки
     *
     * @param $type
     * @param $aData
     * @throws ColumnCountMismatch
     */
    public function insert($type, $aData)
    {
        if(empty(self::$_memory[$type])) {
            self::$_memory[$type] = new Table;
            self::$_memory[$type]->setNames(array_keys($aData));
        }

        self::$_memory[$type]->addRow($aData);
    }

    /**
     * Удаление строк
     *
     * @param $type
     * @param array $aFilter
     * @param array $aOrder
     * @param array $aLimit
     * @throws ColumnCountMismatch
     */
    public function delete($type, $aFilter = [], $aOrder = [], $aLimit = [])
    {
        if(!empty(self::$_memory[$type])) {
            $aFilter = array_intersect_key($aFilter, array_flip(self::$_memory[$type]->getHeader()));
            if($aFilter) {
                self::$_memory[$type]->flush();
                $result = [];
                $headers = self::$_memory[$type]->getHeader();
                while($row = self::$_memory[$type]->fetch(Table::FETCH_ASSOC)) {
                    $diff = false;
                    foreach($aFilter as $k => $v) {
                        if($row[$k] != $v) {
                            $diff = true;
                        }
                    }
                    if($diff) {
                        $result[] = $row;
                    }
                }
                self::$_memory[$type]->fillFromArray($result);
                self::$_memory[$type]->setNames($headers);
            } else {
                self::$_memory[$type]->clear();
            }
        }
    }

    /**
     * Обновление строк
     *
     * @param $type
     * @param $aUpdate
     * @param array $aFilter
     * @param array $aOrder
     * @param array $aLimit
     * @throws ColumnCountMismatch
     */
    public function update($type, $aUpdate, $aFilter = [], $aOrder = [], $aLimit = [])
    {
        self::$_memory[$type]->setRow(0);

        do {
            $row = self::$_memory[$type]->current(Table::FETCH_ASSOC);
            if($row) {
                foreach ($aFilter as $k => $v) {
                    if ($row[$k] != $v) {
                        continue 2;
                    }
                }

                foreach ($aUpdate as $k => $v) {
                    $row[$k] = $v;
                }

                self::$_memory[$type]->replace(
                    self::$_memory[$type]->getPosition(),
                    $row
                );
            }
        } while(self::$_memory[$type]->fetch());

    }
}