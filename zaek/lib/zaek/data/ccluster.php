<?php
namespace zaek\data;

use zaek\engine\CMain;
use zaek\kernel\CException;
use zaek\kernel\CTable;

class CCluster
{
    protected $_app;

    public function __construct(CMain $app)
    {
        $this->_app = $app;
    }

    /**
     * Выборка данных
     *
     * @param $type - Тип данных - название таблицы, файла и т.д.
     * @param array $aFilter - Массив фильтрации
     * @param array $aRange - Массив колонок для выборки
     * @param array $aOrder
     * @param array $aLimit
     * @return CTable
     */
    public function select($type, $aFilter = [], $aRange = [], $aOrder = [], $aLimit = [])
    {
        $table = new CTable();
        $table->setNames($aRange);

        $aData = @parse_ini_string($this->_app->fs()->getContent("%DATA_ROOT%/{$type}.ini.php"));

        if ( $aData && isset($aData['fields']) ) {
            // $aData['fields'] = ['field', 'field_2']
            $aData['fields'] = str_getcsv($aData['fields'], ";");
            $aFilterKeys = array_intersect($aData['fields'], array_keys($aFilter));

            $aData['line'] = array_map(function($v){
                return str_getcsv($v, ";");
            }, $aData['line']);

            // Order
            if ( is_array($aOrder) && count($aOrder) > 0 ) {
                $aOrder = array_intersect_key($aOrder, array_fill_keys($aData['fields'], 0));
                $aOrder = array_combine(
                    array_keys(array_intersect($aData['fields'], array_keys($aOrder))),
                    $aOrder
                );

                if ( count($aOrder) ) {
                    usort($aData['line'], function ($a, $b) use ($aOrder) {
                        foreach ($aOrder as $k => $v) {
                            return ($v == 'ASC') ? strcmp($a[$k], $b[$k]) : strcmp($b[$k], $a[$k]);
                        }
                    });
                }
            }

            // Range
            $aRangeKeys = [];
            foreach ( $aRange as $field ) {
                if ( in_array($field, $aData['fields']) ) {
                    $aRangeKeys[] = [$field, array_search($field, $aData['fields'])];
                }
            }

            foreach ( $aData['line'] as $k => $v ) {
                foreach ( $aFilterKeys as $key => $field) {
                    if ( $v[$key] != $aFilter[$field] ) {
                        unset($aData['line'][$k]);
                        continue 2;
                    }
                }

                $arr = [];

                foreach ( $aRangeKeys as $aKey ) {
                    $arr[] = $aData['line'][$k][$aKey[1]];
                }

                $aData['line'][$k] = $arr;
            }

            // Limit
            if ( is_array($aLimit) && count($aLimit) > 0 ) {
                if ( count($aLimit) == 1 ) {
                    $aData['line'] = array_slice($aData['line'], 0, $aLimit[0]);
                } else if ( count($aLimit) == 2 ) {
                    $aData['line'] = array_slice($aData['line'], $aLimit[0], $aLimit[1]);
                }
            }

            $table->fillFromArray($aData['line']);

        }

        return $table;
    }
}